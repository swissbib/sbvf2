<?php
namespace Swissbib\Tab40Import;

/**
 * Read data from tab40 file
 * Data in tab40 files have a fixed layout
 * See the header of a tab40 file for details about the format
 *
 */
class Reader
{

    /**
     * Read source file into associative data array
     *
     * @param    String        $sourceFile
     * @return    Array[]
     */
    public function read($sourceFile)
    {
        $rawLines    = $this->readLines($sourceFile);
        $lines        = $this->filterLines($rawLines);
        $data        = array();

        foreach ($lines as $line) {
            $data[] = array(
                'code'            => trim(substr($line, 0, 5)),
                'sublibrary'    => trim(substr($line, 5, 5)),
                'label'            => trim(substr($line, 14))
            );
        }

        return $data;
    }



    /**
     * Read file into lines
     * Convert data to utf8
     *
     * @param    String        $sourceFile
     * @return    String[]
     * @throws    Exception
     */
    protected function readLines($sourceFile)
    {
        if (!file_exists($sourceFile)) {
            throw new Exception('File not found "' . $sourceFile . '"');
        }

        $rawLines    = file($sourceFile);
        $rawLines    = array_map('utf8_encode', $rawLines);

        return $rawLines;
    }



    /**
     * Filter out empty and comment lines
     *
     * @param    String[]    $rawLines
     * @return    String[]
     */
    protected function filterLines(array $rawLines)
    {
        foreach ($rawLines as $index => $line) {
                // Commented line
            if ('!' === substr($line, 0, 1)) {
                unset($rawLines[$index]);
            }
            if ('' === trim($line)) {
                unset($rawLines[$index]);
            }
        }

        return $rawLines;
    }
}
