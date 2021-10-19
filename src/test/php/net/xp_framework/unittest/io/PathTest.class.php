<?php namespace net\xp_framework\unittest\io;

use io\{File, Folder, Path};
use lang\{Environment, IllegalArgumentException, IllegalStateException, Runtime};
use unittest\actions\IsPlatform;
use unittest\{Action, Expect, Test, Values};

class PathTest extends \unittest\TestCase {

  /** @return io.File */
  protected function existingFile() { return new File(__FILE__); }

  /** @return io.Folder */
  protected function existingFolder() { return new Folder($this->existingFile()->path); }

  #[Test, Values(['.', '..', 'test', '/root/parent/child', 'C:/Windows', 'C:'])]
  public function create_with_single_argument($arg) {
    new Path($arg);
  }

  #[Test, Values(['.', '..', 'test', '/root/parent/child', 'C:/Windows', 'C:'])]
  public function compose_with_single_argument($arg) {
    Path::compose([$arg]);
  }

  #[Test]
  public function create_combining_multiple_strings() {
    $this->assertEquals(
      '../folder/file.ext',
      (new Path('..', 'folder', 'file.ext'))->toString('/')
    );
  }

  #[Test]
  public function compose_combining_multiple_strings() {
    $this->assertEquals(
      '../folder/file.ext',
      Path::compose(['..', 'folder', 'file.ext'])->toString('/')
    );
  }

  #[Test]
  public function create_combining_strings_and_paths() {
    $this->assertEquals(
      '../folder/file.ext',
      (new Path('..', new Path('folder', 'file.ext')))->toString('/')
    );
  }

  #[Test]
  public function compose_combining_strings_and_paths() {
    $this->assertEquals(
      '../folder/file.ext',
      Path::compose(['..', new Path('folder', 'file.ext')])->toString('/')
    );
  }

  #[Test]
  public function create_combining_files_and_folders() {
    $base= new Folder('folder', 'parent');
    $this->assertEquals(
      $base->getURI().'child'.DIRECTORY_SEPARATOR.'file.ext',
      (new Path($base, 'child', 'file.ext'))->toString()
    );
  }

  #[Test]
  public function compose_combining_files_and_folders() {
    $base= new Folder('folder', 'parent');
    $this->assertEquals(
      $base->getURI().'child'.DIRECTORY_SEPARATOR.'file.ext',
      Path::compose([$base, 'child', 'file.ext'])->toString()
    );
  }

  #[Test]
  public function rooted_folder() {
    $rooted= new Folder('/rooted');
    $this->assertEquals(substr($rooted->getURI(), 0, -1), (new Path($rooted))->toString());
  }

  #[Test]
  public function rooted_file() {
    $rooted= new File('/rooted.ext');
    $this->assertEquals($rooted->getURI(), (new Path($rooted))->toString());
  }

  #[Test]
  public function file_exists() {
    $file= $this->existingFile();
    $this->assertTrue((new Path($file))->exists(), 'Exists: '.$file->getURI());
  }

  #[Test]
  public function folder_exists() {
    $folder= $this->existingFolder();
    $this->assertTrue((new Path($folder))->exists(), 'Exists: '.$folder->getURI());
  }

  #[Test]
  public function is_file() {
    $file= $this->existingFile();
    $this->assertTrue((new Path($file))->isFile(), 'Is a file: '.$file->getURI());
  }

  #[Test]
  public function is_folder() {
    $folder= $this->existingFolder();
    $this->assertTrue((new Path($folder))->isFolder(), 'Is a folder: '.$folder->getURI());
  }

  #[Test]
  public function file_as_uri() {
    $file= $this->existingFile();
    $this->assertEquals($file->getURI(), (new Path($file))->asURI());
  }

  #[Test]
  public function folder_as_uri() {
    $folder= $this->existingFolder();
    $this->assertEquals(rtrim($folder->getURI(), DIRECTORY_SEPARATOR), (new Path($folder))->asURI());
  }

  #[Test]
  public function non_existant_absolute_as_uri() {
    $current= rtrim(realpath(getcwd()), DIRECTORY_SEPARATOR);
    $this->assertEquals(
      $current.DIRECTORY_SEPARATOR.'@does-not-exist@',
      (new Path($current, '@does-not-exist@'))->asURI($current)
    );
  }

  #[Test]
  public function non_existant_relative_as_uri() {
    $current= rtrim(realpath(getcwd()), DIRECTORY_SEPARATOR);
    $this->assertEquals(
      $current.DIRECTORY_SEPARATOR.'@does-not-exist@',
      (new Path('@does-not-exist@'))->asURI($current)
    );
  }

  #[Test]
  public function real_with_folder() {
    $folder= $this->existingFolder();
    $this->assertEquals(rtrim($folder->getURI(), DIRECTORY_SEPARATOR), Path::real($folder)->toString());
  }

  #[Test]
  public function real_with_uri() {
    $folder= $this->existingFolder();
    $this->assertEquals(rtrim($folder->getURI(), DIRECTORY_SEPARATOR), Path::real($folder->getURI())->toString());
  }

  #[Test]
  public function real_with_working_directory() {
    $folder= $this->existingFolder();
    $this->assertEquals($folder->path, Path::real('..', $folder)->toString());
  }

  #[Test]
  public function real_with_array() {
    $folder= $this->existingFolder();
    $this->assertEquals($folder->path, Path::real([$folder->path, '.', $folder->dirname, '..'])->toString());
  }

  #[Test]
  public function folder_as_realpath() {
    $folder= $this->existingFolder();
    $this->assertEquals(
      $folder->path,
      (new Path($folder->path, '.', $folder->dirname, '..'))->asRealpath()->toString()
    );
  }

  #[Test]
  public function folder_as_realpath_with_non_existant_components() {
    $folder= $this->existingFolder();
    $this->assertEquals(
      $folder->path,
      (new Path($folder->path, '.', '@does-', 'not-exist@', '..', '..'))->asRealpath()->toString()
    );
  }

  #[Test, Values([[['@does-', 'not-exist@', '..', '..']], [['.', '@does-', 'not-exist@', '..', '..']]])]
  public function relative_as_realpath_with_non_existant_components($components) {
    $current= realpath(getcwd());
    $this->assertEquals($current, Path::compose($components)->asRealpath($current)->toString());
  }

  #[Test, Action(eval: 'new IsPlatform("!^Win")')]
  public function links_resolved_in_realpath() {
    $temp= Environment::tempDir();
    $link= new Path($temp, 'link-to-temp');
    if (false === symlink($temp, $link)) {
      $this->skip('Cannot create '.$link.' -> '.$temp);
    }

    $resolved= (new Path($link))->asRealpath()->toString();
    unlink($link);

    $this->assertEquals($temp, $resolved);
  }

  #[Test]
  public function as_file() {
    $file= $this->existingFile();
    $this->assertEquals($file, (new Path($file))->asFile());
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => '/.+ is not a file/'])]
  public function as_file_throws_exception_when_invoked_on_a_folder() {
    (new Path($this->existingFolder()))->asFile();
  }

  #[Test]
  public function as_file_works_with_nonexistant_paths() {
    $this->assertEquals(new File('test.txt'), (new Path('test.txt'))->asFile());
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => '/.+ does not exist/'])]
  public function as_file_throws_exception_when_existing_flag_defined_an_nonexistant_path_given() {
    (new Path('test.txt'))->asFile(Path::EXISTING);
  }

  #[Test]
  public function as_folder() {
    $folder= $this->existingFolder();
    $this->assertEquals($folder, (new Path($folder))->asFolder());
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => '/.+ is not a folder/'])]
  public function as_folder_throws_exception_when_invoked_on_a_folder() {
    (new Path($this->existingFile()))->asFolder();
  }

  #[Test]
  public function as_folder_works_with_nonexistant_paths() {
    $this->assertEquals(new Folder('test'), (new Path('test'))->asFolder());
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => '/.+ does not exist/'])]
  public function as_folder_throws_exception_when_existing_flag_defined_an_nonexistant_path_given() {
    (new Path('test'))->asFolder(Path::EXISTING);
  }

  #[Test, Values(['.', '..', 'test', '/root/parent/child', 'C:/Windows'])]
  public function name($arg) {
    $this->assertEquals(basename($arg), (new Path($arg))->name());
  }

  #[Test, Values([['/dev', '/'], ['a/b', 'a'], ['a/b/c', 'a/b'], ['', '..'], ['..', '../..'], ['../a', '../../a'], ['../..', '../../..']])]
  public function parent($child, $parent) {
    $this->assertEquals($parent, (new Path($child))->parent()->toString('/'));
  }

  #[Test, Action(eval: 'new IsPlatform("^Win")')]
  public function parent_of_directory_in_root() {
    $this->assertEquals('C:/', (new Path('C:/Windows'))->parent()->toString('/'));
  }

  #[Test]
  public function parent_of_root() {
    $this->assertNull((new Path('/'))->parent());
  }

  #[Test, Values(['C:', 'C:/', 'c:', 'C:/']), Action(eval: 'new IsPlatform("^Win")')]
  public function parent_of_root_windows($root) {
    $this->assertNull((new Path($root))->parent());
  }

  #[Test, Values([['/dev', true], ['/', true], ['C:/Windows', true], ['C:/', true], ['C:', true], ['', false], ['.', false], ['..', false], ['a', false], ['a/b', false], ['a/b/c', false]])]
  public function absolute($test, $expected) {
    $this->assertEquals($expected, (new Path($test))->isAbsolute());
  }

  #[Test]
  public function existing_file_has_absolute_path() {
    $this->assertTrue((new Path($this->existingFile()))->isAbsolute());
  }

  #[Test]
  public function existing_folder_has_absolute_path() {
    $this->assertTrue((new Path($this->existingFolder()))->isAbsolute());
  }

  #[Test, Values([['a', 'a'], ['a//b', 'a/b'], ['a///b', 'a/b'], ['./a', 'a'], ['././a', 'a'], ['a/./b', 'a/b'], ['a/././b', 'a/b'], ['a/../b', 'b'], ['a/../b/../c', 'c'], ['a/..', '.'], ['a/b/../..', '.'], ['../../a', '../../a'], ['a/../..', '..'],])]
  public function normalize($input, $result) {
    $this->assertEquals($result, (new Path($input))->normalize()->toString('/'));
  }

  #[Test, Values([['.', '.'], ['./.', '.'], ['././.', '.'], ['./..', '..'], ['..', '..'], ['../.', '..'], ['../..', '../..'], ['./../..', '../..'],])]
  public function normalize_dots($input, $result) {
    $this->assertEquals($result, (new Path($input))->normalize()->toString('/'));
  }

  #[Test, Values([['/', '/'], ['/var/lib/', '/var/lib'], ['//', '/'], ['/.', '/'], ['/var/lib/dpkg/..', '/var/lib'], ['/var/lib/.', '/var/lib'], ['C:', 'C:/'], ['C:/', 'C:/'], ['c:/', 'C:/'], ['C:/tools', 'C:/tools'], ['C://', 'C:/'], ['C:/.', 'C:/'], ['C:/tools/..', 'C:/'], ['C:/tools/.', 'C:/tools'],])]
  public function normalize_absolute($input, $result) {
    $this->assertEquals($result, (new Path($input))->normalize()->toString('/'));
  }

  #[Test, Values([['/..', '/'], ['/../..', '/'], ['C:/..', 'C:/'], ['C:/../..', 'C:/'],])]
  public function normalize_dotdot_below_root($input, $result) {
    $this->assertEquals($result, (new Path($input))->normalize()->toString('/'));
  }

  #[Test]
  public function normalize_uppercases_drive_letter() {
    $this->assertEquals('C:/test', (new Path('c:/test'))->normalize()->toString('/'));
  }

  #[Test, Values([['a', 'b', 'a/b'], ['.', 'b', './b'], ['..', 'b', '../b'], ['/var', 'log', '/var/log'], ['C:/Windows', 'system32', 'C:/Windows/system32'], ['/usr/local', '/usr/bin', '../bin'], ['/usr/local/bin', '/usr', '../..'], ['/usr/local', '/usr/local', '']])]
  public function resolve($a, $b, $result) {
    $this->assertEquals($result, (new Path($a))->resolve($b)->toString('/'));
  }

  #[Test, Expect(IllegalArgumentException::class), Values([['relative', '/dev'], ['relative', 'C:/Windows'], ['relative', 'c:/Windows']])]
  public function cannot_resolve_path_if_path_is_relative_and_arg_is_absolute($a, $b) {
    (new Path($a))->relativeTo($b);
  }

  #[Test, Values([['a', 'a', ''], ['', '',  ''], ['..', '..', ''], ['a', '.', 'a'], ['a/b', '.', 'a/b'], ['..', '', '..'], ['../a', '../a', ''], ['xp/core', 'xp', 'core'], ['core/src/main/php', 'core', 'src/main/php'], ['xp/core', 'xp/sequence', '../core'], ['xp/core', 'stubbles/core', '../../xp/core'], ['src', 'src/test/php', '../..'], ['src/main', 'src/main/php', '..'],])]
  public function relative_to($a, $b, $result) {
    $this->assertEquals($result, (new Path($a))->relativeTo($b)->toString('/'));
  }

  #[Test, Values([['/var', '/', 'var'], ['/var', '/var', ''], ['/usr/local', '/usr/bin', '../local'], ['C:/Windows', 'C:/', 'Windows'], ['C:\Windows', 'C:', 'Windows'], ['c:/Windows', 'C:/', 'Windows'], ['C:\Windows', 'c:', 'Windows'], ['/home/seq/src/main/php/Test.php', '/home/compiler', '../seq/src/main/php/Test.php']])]
  public function relative_to_absolute($a, $b, $result) {
    $this->assertEquals($result, (new Path($a))->relativeTo($b)->toString('/'));
  }

  #[Test, Expect(IllegalArgumentException::class), Values([['/dev', 'relative'], ['relative', '/dev'], ['C:/Windows', 'relative'], ['relative', 'C:/Windows'], ['c:/Windows', 'relative'], ['relative', 'c:/Windows']])]
  public function cannot_calculate_relative_path_if_one_component_is_absolute_and_the_other_isnt($a, $b) {
    (new Path($a))->relativeTo($b);
  }

  #[Test]
  public function equals_itself() {
    $fixture= new Path('.');
    $this->assertEquals($fixture, $fixture);
  }

  #[Test]
  public function equals_performs_normalization() {
    $this->assertEquals(new Path('.'), new Path('dir/..'));
  }

  #[Test, Action(eval: 'new IsPlatform("^Win")'), Values([['\\\\remote\\file.txt', true], ['\\\\remote', true]])]
  public function unc_path_is_absolute() {
    $this->assertTrue((new Path('\\\\remote\file.txt'))->isAbsolute());
  }

  #[Test, Action(eval: 'new IsPlatform("^Win")')]
  public function unc_path() {
    $this->assertEquals('//remote/file.txt', (new Path('\\\\remote\file.txt'))->toString('/'));
  }

  #[Test, Action(eval: 'new IsPlatform("^Win")')]
  public function unc_path_as_base() {
    $this->assertEquals('//remote/file.txt', (new Path('\\\\remote', 'file.txt'))->toString('/'));
  }
}