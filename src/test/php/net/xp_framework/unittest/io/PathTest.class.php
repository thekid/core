<?php namespace net\xp_framework\unittest\io;

use io\Path;
use io\File;
use io\Folder;
use lang\Runtime;

class PathTest extends \unittest\TestCase {

  /** @return io.File */
  protected function existingFile() { return new File(Runtime::getInstance()->bootstrapScript('class')); }

  /** @return io.Folder */
  protected function existingFolder() { return new Folder($this->existingFile()->path); }

  #[@test, @values(['.', '..', 'test', '/root/parent/child', 'C:\\Windows', 'C:'])]
  public function create_with_single_argument($arg) {
    new Path($arg);
  }

  #[@test, @values(['.', '..', 'test', '/root/parent/child', 'C:\\Windows', 'C:'])]
  public function compose_with_single_argument($arg) {
    Path::compose([$arg]);
  }

  #[@test]
  public function create_combining_multiple_strings() {
    $this->assertEquals(
      '../folder/file.ext',
      (new Path('..', 'folder', 'file.ext'))->toString('/')
    );
  }

  #[@test]
  public function compose_combining_multiple_strings() {
    $this->assertEquals(
      '../folder/file.ext',
      Path::compose(['..', 'folder', 'file.ext'])->toString('/')
    );
  }

  #[@test]
  public function create_combining_strings_and_paths() {
    $this->assertEquals(
      '../folder/file.ext',
      (new Path('..', new Path('folder', 'file.ext')))->toString('/')
    );
  }

  #[@test]
  public function compose_combining_strings_and_paths() {
    $this->assertEquals(
      '../folder/file.ext',
      Path::compose(['..', new Path('folder', 'file.ext')])->toString('/')
    );
  }

  #[@test]
  public function create_combining_files_and_folders() {
    $base= new Folder('folder', 'parent');
    $this->assertEquals(
      $base->getURI().'child'.DIRECTORY_SEPARATOR.'file.ext',
      (new Path($base, 'child', 'file.ext'))->toString()
    );
  }

  #[@test]
  public function compose_combining_files_and_folders() {
    $base= new Folder('folder', 'parent');
    $this->assertEquals(
      $base->getURI().'child'.DIRECTORY_SEPARATOR.'file.ext',
      Path::compose([$base, 'child', 'file.ext'])->toString()
    );
  }

  #[@test]
  public function rooted_folder() {
    $rooted= new Folder('/rooted');
    $this->assertEquals(substr($rooted->getURI(), 0, -1), (new Path($rooted))->toString());
  }

  #[@test]
  public function rooted_file() {
    $rooted= new File('/rooted.ext');
    $this->assertEquals($rooted->getURI(), (new Path($rooted))->toString());
  }

  #[@test]
  public function file_exists() {
    $file= $this->existingFile();
    $this->assertTrue((new Path($file))->exists(), 'Exists: '.$file->getURI());
  }

  #[@test]
  public function folder_exists() {
    $folder= $this->existingFolder();
    $this->assertTrue((new Path($folder))->exists(), 'Exists: '.$folder->getURI());
  }

  #[@test]
  public function is_file() {
    $file= $this->existingFile();
    $this->assertTrue((new Path($file))->isFile(), 'Is a file: '.$file->getURI());
  }

  #[@test]
  public function is_folder() {
    $folder= $this->existingFolder();
    $this->assertTrue((new Path($folder))->isFolder(), 'Is a folder: '.$folder->getURI());
  }

  #[@test]
  public function file_as_uri() {
    $file= $this->existingFile();
    $this->assertEquals($file->getURI(), (new Path($file))->asURI());
  }

  #[@test]
  public function folder_as_uri() {
    $folder= $this->existingFolder();
    $this->assertEquals(rtrim($folder->getURI(), DIRECTORY_SEPARATOR), (new Path($folder))->asURI());
  }

  #[@test]
  public function non_existant_absolute_as_uri() {
    $current= rtrim(getcwd(), DIRECTORY_SEPARATOR);
    $this->assertEquals(
      $current.DIRECTORY_SEPARATOR.'@does-not-exist@',
      (new Path($current, '@does-not-exist@'))->asURI($current)
    );
  }

  #[@test]
  public function non_existant_relative_as_uri() {
    $current= rtrim(getcwd(), DIRECTORY_SEPARATOR);
    $this->assertEquals(
      $current.DIRECTORY_SEPARATOR.'@does-not-exist@',
      (new Path('@does-not-exist@'))->asURI($current)
    );
  }

  #[@test]
  public function folder_as_realpath() {
    $folder= $this->existingFolder();
    $this->assertEquals(
      $folder->path,
      (new Path($folder->path, '.', $folder->dirname, '..'))->asRealpath()->toString()
    );
  }

  #[@test]
  public function folder_as_realpath_with_non_existant_components() {
    $folder= $this->existingFolder();
    $this->assertEquals(
      $folder->path,
      (new Path($folder->path, '.', '@does-', 'not-exist@', '..', '..'))->asRealpath()->toString()
    );
  }

  #[@test, @values([
  #  [['@does-', 'not-exist@', '..', '..']],
  #  [['.', '@does-', 'not-exist@', '..', '..']]
  #])]
  public function relative_as_realpath_with_non_existant_components($components) {
    $current= getcwd();
    $this->assertEquals($current, Path::compose($components)->asRealpath($current)->toString());
  }

  #[@test]
  public function links_resolved_in_realpath() {
    $temp= \lang\System::tempDir();
    $link= new Path($temp, 'link-to-temp');
    if (false === symlink($temp, $link)) {
      $this->skip('Cannot create '.$link.' -> '.$temp);
    }

    $resolved= (new Path($link))->asRealpath()->toString();
    unlink($link);

    $this->assertEquals($temp, $resolved);
  }

  #[@test]
  public function as_file() {
    $file= $this->existingFile();
    $this->assertEquals($file, (new Path($file))->asFile());
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= '/.+ is not a file/')]
  public function as_file_throws_exception_when_invoked_on_a_folder() {
    (new Path($this->existingFolder()))->asFile();
  }

  #[@test]
  public function as_folder() {
    $folder= $this->existingFolder();
    $this->assertEquals($folder, (new Path($folder))->asFolder());
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= '/.+ is not a folder/')]
  public function as_folder_throws_exception_when_invoked_on_a_folder() {
    (new Path($this->existingFile()))->asFolder();
  }

  #[@test, @values(['.', '..', 'test', '/root/parent/child', 'C:\\Windows'])]
  public function name($arg) {
    $this->assertEquals(basename($arg), (new Path($arg))->name());
  }

  #[@test, @values([
  #  ['/dev', '/'],
  #  ['C:\\Windows', 'C:/'],
  #  ['a/b', 'a'], ['a/b/c', 'a/b'],
  #  ['', '..'], ['..', '../..'], ['../a', '../../a'], ['../..', '../../..']
  #])]
  public function parent($child, $parent) {
    $this->assertEquals($parent, (new Path($child))->parent()->toString('/'));
  }

  #[@test, @values(['/', 'C:', 'C:\\'])]
  public function parent_of_root($root) {
    $this->assertNull((new Path($root))->parent());
  }

  #[@test, @values([
  #  ['/dev', true], ['/', true],
  #  ['C:\\Windows', true], ['C:\\', true], ['C:', true],
  #  ['', false], ['.', false], ['..', false],
  #  ['a', false],
  #  ['a/b', false], ['a/b/c', false]
  #])]
  public function absolute($test, $expected) {
    $this->assertEquals($expected, (new Path($test))->isAbsolute());
  }

  #[@test]
  public function existing_file_has_absolute_path() {
    $this->assertTrue((new Path($this->existingFile()))->isAbsolute());
  }

  #[@test]
  public function existing_folder_has_absolute_path() {
    $this->assertTrue((new Path($this->existingFolder()))->isAbsolute());
  }

  #[@test, @values([
  #  ['a', 'a'],
  #  ['a//b', 'a/b'], ['a///b', 'a/b'],
  #  ['./a', 'a'], ['././a', 'a'],
  #  ['a/./b', 'a/b'], ['a/././b', 'a/b'],
  #  ['a/../b', 'b'], ['a/../b/../c', 'c'],
  #  ['a/..', ''], ['a/b/../..', ''],
  #  ['..', '..'], ['../..', '../..'], ['../../a', '../../a'],
  #  ['./..', '..'], ['./../..', '../..'], ['a/../..', '..']
  #])]
  public function normalize($input, $result) {
    $this->assertEquals($result, (new Path($input))->normalize()->toString('/'));
  }

  #[@test, @values([
  #  ['a', 'b', 'a/b'],
  #  ['.', 'b', './b'], ['..', 'b', '../b'],
  #  ['/var', 'log', '/var/log'],
  #  ['C:\\Windows', 'system32', 'C:/Windows/system32'],
  #  ['/usr/local', '/usr/bin', '../bin'],
  #  ['/usr/local/bin', '/usr', '../..'],
  #  ['/usr/local', '/usr/local', '']
  #])]
  public function resolve($a, $b, $result) {
    $this->assertEquals($result, (new Path($a))->resolve($b)->toString('/'));
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([
  #  ['relative', '/dev'],
  #  ['relative', 'C:\\Windows']
  #])]
  public function cannot_resolve_path_if_path_is_relative_and_arg_is_absolute($a, $b) {
    (new Path($a))->relativeTo($b);
  }

  #[@test, @values([
  #  ['a', 'a', ''], ['', '',  ''], ['..', '..', ''],
  #  ['a', '.', 'a'], ['a/b', '.', 'a/b'],
  #  ['..', '', '..'], ['../a', '../a', ''],
  #  ['xp/core', 'xp', 'core'], ['core/src/main/php', 'core', 'src/main/php'],
  #  ['xp/core', 'xp/sequence', '../core'],
  #  ['xp/core', 'stubbles/core', '../../xp/core'],
  #  ['src', 'src/test/php', '../..'], ['src/main', 'src/main/php', '..'],
  #  ['/var', '/', 'var'],
  #  ['/var', '/var', ''], ['/usr/local', '/usr/bin', '../local'],
  #  ['C:\\Windows', 'C:\\', 'Windows'], ['C:\Windows', 'C:', 'Windows']
  #])]
  public function relativeTo($a, $b, $result) {
    $this->assertEquals($result, (new Path($a))->relativeTo($b)->toString('/'));
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([
  #  ['/dev', 'relative'], ['relative', '/dev'],
  #  ['C:\\Windows', 'relative'], ['relative', 'C:\\Windows']
  #])]
  public function cannot_calculate_relative_path_if_one_component_is_absolute_and_the_other_isnt($a, $b) {
    (new Path($a))->relativeTo($b);
  }
}