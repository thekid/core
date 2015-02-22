<?php namespace lang\codedom;

use lang\FormatException;

class PhpSyntax extends \lang\Object {
  private static $parse;

  static function __static() {
    $type= new Tokens([T_STRING, T_NS_SEPARATOR]);
    $modifiers= new Tokens([T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FINAL, T_ABSTRACT, T_WHITESPACE]);

    self::$parse= [
      ':start' => new Sequence([new Token(T_OPEN_TAG), new Rule(':namespace'), new Rule(':imports'), new Rule(':uses_opt'), new Rule(':declaration')], function($values) {
        $imports= $values[2];
        foreach ($values[3] as $uses) {
          $imports= array_merge($imports, (array)$uses);
        }
        return new CodeUnit($values[1], $imports, $values[4]);
      }),
      ':namespace' => new Optional(new OneOf([
        T_VARIABLE  => new Sequence([new Token('='), new Token(T_CONSTANT_ENCAPSED_STRING), new Token(';')], function($values) {
          return strtr(substr($values[2], 1, -1), '.', '\\');
        }),
        T_NAMESPACE => new Sequence([$type, new Token(';')], function($values) {
          return implode('', $values[1]);
        })
      ])),
      ':imports' => new AnyOf([
        T_USE => new Sequence([$type, new Token(';')], function($values) { return implode('', $values[1]); }),
        T_NEW => new Sequence([new Token(T_STRING), new Token('('), new Token(T_CONSTANT_ENCAPSED_STRING), new Token(')'), new Token(';')], function($values) {
          return strtr(trim($values[3], '\'"'), '.', '\\');
        })
      ]),
      ':uses_opt' => new AnyOf([
        T_STRING => new Sequence([new Token('('), new SkipOver('(', ')'), new Token(';')], function($values) {
          if ('uses' === $values[0]) {
            return array_map(function($class) { return strtr(trim($class, "'\" "), '.', '\\'); }, explode(',', $values[2]));
          } else {
            return null;
          }
        })
      ]),
      ':declaration' => new Sequence(
        [
          new Rule(':annotations'),
          $modifiers,
          new OneOf([
            T_CLASS     => new Sequence([new Token(T_STRING), new Rule(':class_parent'), new Rule(':class_implements'), new Rule(':type_body')], function($values) {
              return new ClassDeclaration(0, null, $values[1], $values[2], (array)$values[3], $values[4]);
            }),
            T_INTERFACE => new Sequence([new Token(T_STRING), new Rule(':interface_parents'), new Rule(':type_body')], function($values) {
              return new InterfaceDeclaration(0, null, $values[1], (array)$values[2], $values[3]);
            }),
            T_TRAIT => new Sequence([new Token(T_STRING), new Rule(':type_body')], function($values) {
              return new TraitDeclaration(0, null, $values[1], $values[2]);
            })
          ])
        ],
        function($values) {
          $values[2]->annotate($values[0]);
          $values[2]->access(self::modifiers($values[1]));
          return $values[2];
        }
      ),
      ':annotations' => new Optional(new Sequence([new Token(600)], function($values) {
        return $values[0];
      })),
      ':class_parent' => new Optional(
        new Sequence([new Token(T_EXTENDS), $type], function($values) { return implode('', $values[1]); })
      ),
      ':class_implements' => new Optional(
        new Sequence([new Token(T_IMPLEMENTS), new ListOf($type)], function($values) {
          return array_map(function($v) { return implode('', $v); }, $values[1]);
        })
      ),
      ':interface_parents' => new Optional(
        new Sequence([new Token(T_EXTENDS), new ListOf($type)], function($values) {
          return array_map(function($v) { return implode('', $v); }, $values[1]);
        })
      ),
      ':type_body' => new Sequence([new Token('{'), new Repeated(new Rule(':member')), new Token('}')], function($values) {
        $body= ['member' => [], 'trait' => []];
        foreach ($values[1] as $decl) {
          foreach ($decl as $part) {
            $body[$part->type()][]= $part;
          }
        }
        return new TypeBody($body['member'], $body['trait']);
      }),
      ':member' => new EitherOf([
        new Sequence([new Token(T_USE), $type, new Token(';')], function($values) {
          return [new TraitUsage(implode('', $values[1]))];
        }),
        new Sequence([new Token(T_CONST), new ListOf(new Rule(':const')), new Token(';')], function($values) {
          return $values[1];
        }),
        new Sequence(
          [
            new Rule(':annotations'),
            $modifiers,
            new Rule(':annotations'),   // Old way of annotating fields, in combination with grouped syntax
            new OneOf([
              T_FUNCTION => new Sequence([new Token(T_STRING), new Token('('), new SkipOver('(', ')'), new Rule(':method')], function($values) {
                return new MethodDeclaration(0, null, $values[1], $values[3], null, $values[4]);
              }),
              T_VARIABLE => new Sequence([new Rule(':field')], function($values) {
                return new FieldDeclaration(0, null, substr($values[0], 1), $values[1]);
              })
            ])
          ],
          function($values) {
            $values[0] && $values[3]->annotate($values[0]);
            $values[2] && $values[3]->annotate($values[2]);
            $values[3]->access(self::modifiers($values[1]));
            return [$values[3]];
          }
        ),
      ]),
      ':const' => new Sequence([new Token(T_STRING), new Token('='), new Expr()], function($values) {
        return new ConstantDeclaration($values[0], $values[2]);
      }),
      ':field' => new Sequence(
        [
          new Optional(new Sequence([new Token('='), new Expr()], function($values) { return $values[1]; })),
          new OneOf([';' => new Returns(null), ',' => new Returns(null)])
        ],
        function($values) { return $values[0]; }
      ),
      ':method' => new OneOf([
        ';' => new Returns(null),
        '{' => new Sequence([new SkipOver('{', '}')], function($values) { return $values[1]; })
      ])
    ];
  }

  /**
   * Parses modifier names into flags
   *
   * @param  string[] $names
   * @return int
   */
  protected static function modifiers($names) {
    static $modifiers= [
      'public'    => MODIFIER_PUBLIC,
      'private'   => MODIFIER_PRIVATE,
      'protected' => MODIFIER_PROTECTED,
      'static'    => MODIFIER_STATIC,
      'final'     => MODIFIER_FINAL,
      'abstract'  => MODIFIER_ABSTRACT
    ];

    $m= 0;
    foreach ($names as $name) {
      isset($modifiers[$name]) && $m |= $modifiers[$name];
    }
    return $m;
  }

  /**
   * Parses input
   *
   * @param  string $input
   * @return lang.codedom.CodeUnit
   * @throws lang.FormatException
   */
  public function parse($input) {
    return self::$parse[':start']->evaluate(self::$parse, new Stream($input));
  }
}