<?php
/**
 * Safe deal service
 *
 * @package sbr
 * @author Cototnti team
 * @copyright Cototnti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\sbr\inc;

use Cot;
use cot\traits\GetInstanceTrait;

class SbrFileService
{
    use GetInstanceTrait;

    /**
     * Extracts filename extension with tar (.tar.gz, tar.bz2, etc.) support.
     *
     * @param string $filename File name
     * @return string|false File extension or false on error
     */
    public function getFileExtension(string $fileName): ?string
    {
        if (preg_match('#((\.tar)?\.\w+)$#', $fileName, $m)) {
            return mb_strtolower(mb_substr($m[1], 1));
        }

        return null;
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @return string|true TRUE or error message
     */
    public function validateUploadedFile(string $filePath, string $fileName)
    {
        $maxFileSize = ((int) Cot::$cfg['plugin']['sbr']['maxUploadSize'] * 1024 * 1024);
        if ($maxFileSize > 0) {
            $fileSize = filesize($filePath);
            if ($fileSize > $maxFileSize) {
                return cot_rc(Cot::$L['sbr_error_fileToLarge'], ['size' => Cot::$cfg['plugin']['sbr']['maxUploadSize']]);
            }
        }

        $allowedExtensions = explode(',', Cot::$cfg['plugin']['sbr']['extensions']);
        if (!empty($allowedExtensions)) {
            foreach ($allowedExtensions as $key => $ext) {
                $ext = trim($ext);
                if ($ext === '') {
                    unset($allowedExtensions[$key]);
                    continue;
                }

                $allowedExtensions[$key] = mb_strtolower($ext);
            }
        }

        $fileExtension = $this->getFileExtension($fileName);
        if ($fileExtension !== null) {
            $fileExtension = mb_strtolower($fileExtension);
        }

        if (!empty($allowedExtensions)) {
            if (
                $fileExtension === null
                || !in_array($fileExtension, $allowedExtensions)
            ) {
                return Cot::$L['sbr_error_invalidFileType'];
            }
        }

        $pfsFileCheck = Cot::$cfg['pfs']['pfsfilecheck'] ?? null;
        $pfsNoMimePass = Cot::$cfg['pfs']['pfsnomimepass'] ?? null;
        Cot::$cfg['pfs']['pfsfilecheck'] = true;
        Cot::$cfg['pfs']['pfsnomimepass'] = true;
        $result = cot_file_check($filePath, $fileName, $fileExtension);
        Cot::$cfg['pfs']['pfsfilecheck'] = $pfsFileCheck;
        Cot::$cfg['pfs']['pfsnomimepass'] = $pfsNoMimePass;

        if (!$result) {
            return Cot::$L['sbr_error_invalidFileType'];
        }

        return true;
    }
}