<?php

class ZipArchiveExt extends ZipArchive
{
    public function extractSubdirTo($destination, $subdir)
    {
        $errors = array();
        // Prepare dirs
        $destination = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $destination);
        $subdir = str_replace(array('/', '\\'), '/', $subdir);
        if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, 'UTF-8') * -1) != DIRECTORY_SEPARATOR) {
            $destination .= DIRECTORY_SEPARATOR;
        }
        $inputSubdir = $subdir;
        $subdir = rtrim($subdir, '/') . '/';
        $folderExists = false;
        for ($i = 0; $i < $this->numFiles; ++$i) {
            $filename = $this->getNameIndex($i);
            if (!$folderExists && $filename == $subdir) {
                $folderExists = true;
            }
            if (substr($filename, 0, mb_strlen($subdir, 'UTF-8')) == $subdir) {
                $relativePath = substr($filename, mb_strlen($subdir, 'UTF-8'));
                $relativePath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $relativePath);
                if (mb_strlen($relativePath, 'UTF-8') > 0) {
                    if (substr($filename, -1) == '/') {
                        if (!is_dir($destination . $relativePath)) {
                            if (!mkdir($destination . $relativePath, 0755, true)) {
                                $errors[$i] = $filename;
                            }
                        }
                    } else {
                        if (dirname($relativePath) != '.') {
                            if (!is_dir($destination . dirname($relativePath))) {
                                // New dir (for file)
                                mkdir($destination . dirname($relativePath), 0755, true);
                            }
                        }
                        if (file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false) {
                            $errors[$i] = $filename;
                        }
                    }
                }
            }
        }

        if (!$folderExists) {
            throw new Exception(sprintf("Folder %s doesn't exists in zip file", $inputSubdir));
        }

        return $errors;
    }
}
