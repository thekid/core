<?php namespace lang\unittest;

use io\IOException;
use lang\Process;
use test\verify\Runtime;
use test\{After, Assert, Before, Expect, Test, Values};

class ProcessResolveTest {
  private $origDir;

  #[Before]
  public function setUp() {
    $this->origDir= getcwd();
  }
  
  #[After]
  public function tearDown() {
    chdir($this->origDir);
  }

  /**
   * Replaces backslashes in the specified path by the new separator. If $skipDrive is set
   * to TRUE, the leading drive letter definition (e.g. 'C:') is removed from the new path.
   *
   * @param  string $path
   * @param  string $newSeparator
   * @param  bool $skipDrive
   * @return string
   */
  private function replaceBackslashSeparator($path, $newSeparator, $skipDrive) {
    $parts= explode('\\', $path);
    if (preg_match('/[a-z]:/i', $parts[0]) != 0 && $skipDrive) array_shift($parts);
    return implode($newSeparator, $parts);
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveFullyQualifiedWithDriverLetter() {
    Assert::true(is_executable(Process::resolve(getenv('WINDIR').'\\EXPLORER.EXE')));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveFullyQualifiedWithDriverLetterWithoutExtension() {
    Assert::true(is_executable(Process::resolve(getenv('WINDIR').'\\EXPLORER')));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveFullyQualifiedWithBackSlash() {
    $path= '\\'.$this->replaceBackslashSeparator(getenv('WINDIR').'\\EXPLORER.EXE', '\\', TRUE);
    chdir('C:');
    Assert::true(is_executable(Process::resolve($path)));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveFullyQualifiedWithSlash() {
    $path= '/'.$this->replaceBackslashSeparator(getenv('WINDIR').'\\EXPLORER.EXE', '/', TRUE);
    chdir('C:');
    Assert::true(is_executable(Process::resolve($path)));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveFullyQualifiedWithoutExtension() {
    $path='\\'.$this->replaceBackslashSeparator(getenv('WINDIR').'\\EXPLORER', '\\', TRUE);
    chdir('C:');
    Assert::true(is_executable(Process::resolve($path)));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveCommandInPath() {
    Assert::true(is_executable(Process::resolve('explorer.exe')));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveCommandInPathWithoutExtension() {
    Assert::true(is_executable(Process::resolve('explorer')));
  }

  #[Test, Expect(IOException::class)]
  public function resolveSlashDirectory() {
    Process::resolve('/');
  }

  #[Test, Runtime(os: 'WIN'), Expect(IOException::class)]
  public function resolveBackslashDirectory() {
    Process::resolve('\\');
  }

  #[Test, Expect(IOException::class)]
  public function resolveEmpty() {
    Process::resolve('');
  }

  #[Test, Expect(IOException::class)]
  public function resolveNonExistant() {
    Process::resolve('@@non-existant@@');
  }

  #[Test, Expect(IOException::class)]
  public function resolveNonExistantFullyQualified() {
    Process::resolve('/@@non-existant@@');
  }

  #[Test, Runtime(os: 'ANDROID')]
  public function resolveFullyQualifiedOnAndroid() {
    $fq= getenv('ANDROID_ROOT').'/framework/core.jar';
    Assert::equals($fq, Process::resolve($fq));
  }

  #[Test, Runtime(os: '^(?!WIN|ANDROID)')]
  public function resolveFullyQualifiedOnPosix() {
    Assert::true(in_array(Process::resolve('/bin/ls'), ['/usr/bin/ls', '/bin/ls']));
  }

  #[Test, Values(['"ls"', "'ls'"]), Runtime(os: '^(?!WIN|ANDROID)')]
  public function resolveQuotedOnPosix($command) {
    Assert::true(in_array(Process::resolve($command), ['/usr/bin/ls', '/bin/ls']));
  }

  #[Test, Runtime(os: 'WIN')]
  public function resolveQuotedOnWindows() {
    Assert::true(is_executable(Process::resolve('"explorer"')));
  }

  #[Test, Runtime(os: '^(?!WIN|ANDROID)')]
  public function resolve() {
    Assert::true(in_array(Process::resolve('ls'), ['/usr/bin/ls', '/bin/ls']));
  }
}