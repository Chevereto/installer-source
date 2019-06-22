<?php
/* --------------------------------------------------------------------

    Chevereto Installer : Make
    http://chevereto.com/

      /$$$$$$  /$$                                                           /$$
     /$$__  $$| $$                                                          | $$
    | $$  \__/| $$$$$$$   /$$$$$$  /$$    /$$ /$$$$$$   /$$$$$$   /$$$$$$  /$$$$$$    /$$$$$$
    | $$      | $$__  $$ /$$__  $$|  $$  /$$//$$__  $$ /$$__  $$ /$$__  $$|_  $$_/   /$$__  $$
    | $$      | $$  \ $$| $$$$$$$$ \  $$/$$/| $$$$$$$$| $$  \__/| $$$$$$$$  | $$    | $$  \ $$
    | $$    $$| $$  | $$| $$_____/  \  $$$/ | $$_____/| $$      | $$_____/  | $$ /$$| $$  | $$
    |  $$$$$$/| $$  | $$|  $$$$$$$   \  $/  |  $$$$$$$| $$      |  $$$$$$$  |  $$$$/|  $$$$$$/
     \______/ |__/  |__/ \_______/    \_/    \_______/|__/       \_______/   \___/   \______/


    Creates a single-file version of this installer.

    Command: php make.php
    Output: output/installer.php

    This make is very basic and doesn't work with sub-includes and is very sensitive to quotes and whatnot.

  --------------------------------------------------------------------- */
declare(strict_types=1);
new Make('app.php', 'build/installer.php');

// $appContents = str_replace("require 'template/content.php';", )

class Make
{
    /** @var string */
    public $contents;

    public function __construct(string $sourceFilepath, string $targetFilepath)
    {
        $this->sourceFilepath = realpath($sourceFilepath);
        $this->targetFilepath = realpath($targetFilepath);
        $this->contents = file_get_contents($sourceFilepath);
        $this->putTemplate('template/content.php');
        $this->replacePHPFile("/include '(.*)';/");
        $this->replacePHPFile("/require '(.*)';/");
        $this->replaceTextFile("/file_get_contents\(\'(.*)\'\)\;/");
        $this->writeFile($this->targetFilepath, $this->contents);
    }

    protected function putTemplate(string $templateFilepath)
    {
        $find = [
          'ob_start();',
          "require '$templateFilepath';",
          '$content = ob_get_clean();',
          '<?php echo $content; ?>',
        ];
        $replace = [
          null,
          null,
          null,
          file_get_contents($templateFilepath),
        ];
        $this->contents = str_replace($find, $replace, $this->contents);
    }

    protected function replacePHPFile(string $regex)
    {
        preg_match_all($regex, $this->contents, $includes);
        foreach ($includes[0] as $k => $find) {
            $fileContents = $this->getFileContents($includes[1][$k]);
            $this->contents = str_replace($find, $fileContents, $this->contents);
        }
    }

    protected function replaceTextFile(string $regex)
    {
        preg_match_all($regex, $this->contents, $files);
        foreach ($files[0] as $k => $find) {
            $fileContents = $this->getFileContents($files[1][$k]);
            $this->contents = str_replace($find, var_export($fileContents, true).';', $this->contents);
        }
    }

    protected function getFileContents(string $filepath)
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $fileContents = file_get_contents($filepath);
        if ('php' == $ext) {
            $fileContents = str_replace('<?php', null, $fileContents);
        }
        $fileContents = trim($fileContents);

        return $fileContents;
    }

    protected function writeFile(string $filepath, string $contents)
    {
        $fh = fopen($filepath, 'w');
        if (!$fh || !fwrite($fh, $contents)) {
            trigger_error(sprintf('Unable to create %s file', $filepath));
        }
        fclose($fh);
    }
}