<?php

abstract class fsDataType_CSV extends fsDataType_Abstract
{
    /**
     * Things I'm trying to handle (e.g. should write tests for)
     *
     * 1. trim all cells
     * 2. skip empty rows
     * 3. dont give an error for different length rows
     */
    public static function read($path) {
    	ini_set("auto_detect_line_endings", true);
        if (!is_file($path)) return array();

        $file_stream = fopen($path, 'r');
        $data = array();

        $headers = self::read_row($file_stream);

        while ($row = self::read_row($file_stream, $headers)) {
            $data[] = $row;
        }

        fclose($file_stream);
        return $data;
    }

    public static function overwrite($path, $data) {
    	ini_set("auto_detect_line_endings", true);
        $dir = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $data = self::trim_headers($data);
        $file_stream = fopen($path, 'w');
        $headers = array();

        foreach ($data as $row) {
            foreach ($row as $header => $val) {
                $headers[$header] = true;
            }
        }

        $headers = array_keys($headers);
        $written = fputcsv($file_stream, $headers);
        $written += self::write_rows($file_stream, $headers, $data);
        fclose($file_stream);
        return $written;
    }

    /**
     * Things I'm trying to handle (e.g. should write tests for)
     *
     * 1. if file doesnt exist, create row and write headers
     * 2. if new columns are being written, add columns to first row of file
     * 3. sort row in order of the headers of this file
     * 4. make sure I'm not allowing characters that break formatting when file is opened in Excel
     */
    public static function write($path, $data, $index = null) {
    	ini_set("auto_detect_line_endings", true);
        if ($index !== null && (!is_numeric($index) || $index < 0)) {
            throw new Exception('Csv append index must be null or non-negative number');
        }

        if (is_numeric($index)) {
            return self::rewrite_line($path, $data, $index);
        } else {
            return self::write_many($path, array($data));
        }
    }

    public static function write_many($path, $data) {
        if (!is_file($path)) {
            return self::overwrite($path, $data);
        }

		ini_set("auto_detect_line_endings", true);
		
        $data           = self::trim_headers($data);
        $file_stream     = fopen($path, 'r+');
        $old_headers     = fgetcsv($file_stream);
        $old_headers_flip = array_flip($old_headers);
        $new_headers_flip = array();

        foreach ($data as $row) {
            foreach ($row as $col => $cell) {
                if (!isset($old_headers_flip[$col])) {
                    $new_headers_flip[$col] = true;
                }
            }
        }

        if ($new_headers_flip !== array()) {
            $final_headers = array_merge($old_headers, array_keys($new_headers_flip));
            $old_data = stream_get_contents($file_stream);
            rewind($file_stream);

            fputcsv($file_stream, $final_headers);
            fwrite($file_stream, $old_data);

        } else {
            fseek($file_stream, 0, SEEK_END);
            $final_headers = $old_headers;
        }

        $written = self::write_rows($file_stream, $final_headers, $data);
        fclose($file_stream);
        return $written;
    }

    private static function write_rows($file_stream, $headers, $rows) {
        $written = 0;

        foreach ($rows as $row) {
            $sorted_row = array();

            foreach ($headers as $header) {
                $sorted_row[$header] = isset($row[$header]) ? $row[$header] : '';
            }

            $written += fputcsv($file_stream, $sorted_row);
        }

        return $written;
    }

    private static function read_row($file_stream, $headers = null) {
        $row_is_empty = true;

        while ($row_is_empty) {
            $row = fgetcsv($file_stream);

            if ($row === false || $row === null) return $row;

            $clean_row = array();

            foreach ($row as $cell) {
                if (($clean_row[] = trim($cell)) !== '') $row_is_empty = false;
            }
        }

        if ($headers === null) {
            return $row;
        } else {
            $sorted_row = array();

            foreach ($headers as $i => $header) {
                $sorted_row[$header] = isset($row[$i]) ? $row[$i] : '';
            }

            return $sorted_row;
        }
    }

    private static function trim_headers($data) {
        $trimmed_data = array();

        foreach ($data as $i => $row) {
            foreach ($row as $header => $cell) {
                $trimmed_data[$i][trim($header)] = $cell;
            }
        }

        return $trimmed_data;
    }

    private static function rewrite_line($path, $data, $index) {
        $index = (int) $index;
        $file_stream = fopen($path, 'r+');
        $headers = self::read_row($file_stream);
        $current_index = 0;
        $new_data = self::trim_headers(array($data));

        do {
            if ($current_index === $index) {
                $rewrite_offset = ftell($file_stream);
                self::read_row($file_stream); // skip this row, we are about to overwrite
                $remaining_data = stream_get_contents($file_stream);
                fseek($file_stream, $rewrite_offset, SEEK_SET); // go back to the beginning of the row we are replacing
                $written = self::write_rows($file_stream, $headers, $new_data);
                fwrite($file_stream, $remaining_data);
                fclose($file_stream);
                return $written;
            }

            ++$current_index;
        } while (self::read_row($file_stream));

        // turns out, the row we are replacing doesnt exist
        // so, create some padding rows
        $padding_content = array_pad(array(), count($headers), '_');
        $padding_content = array_combine($headers, $padding_content);
        $padding_row_count = $index - $current_index + 1;
        $all_new_data = array_pad(array(), $padding_row_count, $padding_content); // padding rows
        $all_new_data[] = $new_data[0]; // add new row to the end of padding
        $written = self::write_rows($file_stream, $headers, $all_new_data);
        fclose($file_stream);
        return $written;
    }
    
    public static function get_columns($path) {
        if (!is_file($path)) return array();

		ini_set("auto_detect_line_endings", true);

        $file_stream = fopen($path, 'r');

        $columns = self::read_row($file_stream);

        fclose($file_stream);
        return $columns;
    }
}
