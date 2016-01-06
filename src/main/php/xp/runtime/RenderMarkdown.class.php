<?php namespace xp\runtime;

/**
 * Converts markdown to plain text w/ ASCII "art". Does not assume
 * to be a full-fledged, spec-compliant markdown parser!
 *
 * @test  xp://net.xp_framework.unittest.runtime.RenderMarkdownTest
 */
class RenderMarkdown {
  private $style;

  /**
   * Creates markdown
   *
   * @param  [:string] $style
   */
  public function __construct($style) {
    $this->style= $style;
  }

  /**
   * Converts api-doc "markup" to plain text w/ ASCII "art"
   *
   * @param  string $markdown
   * @return string text
   */
  public function render($markdown) {
    $style= $this->style;
    return preg_replace(
      [
        '/# (.+)/',                              // Prefixed first-level headline
        '/\*\*([^\*]+)\*\*/',                    // **bold**
        '/\*([^ ][^\*]+[^ ])\*/',                // *italic*
        '/`([^`]+)`/'                            // `preformat`
      ],
      [$style['h1'], $style['bold'], $style['italic'], $style['pre']],
      preg_replace(
        [
          '/^(.+)\n=+$/m',                       // Underlined first-level headline
          '/^\* \* \*$/m',                       // horizontal rule
          '/^([*+-]) (.+)$/m',                   // unordered list
          '/^( *)```([a-z]*)\n *(.+)\n *```$/m', // Code section
        ],
        [$style['h1'], $style['hr'], $style['li'], $style['code']],
        trim($markdown, "\r\n")
      )
    );
  }
}