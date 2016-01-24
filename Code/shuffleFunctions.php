<?php
/**
 * The functions responsible for handling all shuffling in Collector.
 */

/**
 * [Description].
 *
 * @param array  $settings    The (by-reference) array of settings to search.
 * @param string $target      The setting to search for.
 * @param bool   $removeFound Indicates whether the setting should be unset if 
 *                            found. True by default.
 *
 * @return string|bool The setting if it existed, else false.
 *
 * @todo description for find setting
 */
function findSetting(array &$settings, $target, $removeFound = true)
{
    foreach ($settings as $key => $setting) {
        $test = removeLabel($setting, $target);
        if ($test !== false) {
            if ($removeFound) {
                unset($settings[$key]);
            }

            return $test;
        }
    }

    return false;
}

/**
 * Shuffles the values in an array (by-reference) while keeping the order of
 * the keys intact.
 * 
 * @param array $array The (by-reference) array to shuffle.
 */
function shuffleAssoc(&$array)
{
    $keys = array_keys($array);
    $vals = array_values($array);
    shuffle($vals);
    foreach ($keys as $i => $key) {
        $array[$key] = $vals[$i];
    }
}

/**
 * Helper function to generate the message for shuffle exceptions.
 * 
 * @param string $msg         The message to append to the common message.
 * @param string $errorOrigin The origin of the error.
 * 
 * @return string The full exception message.
 */
function getShuffleExceptionMsg($msg, $errorOrigin)
{
    return "Search error within the 'Target' or 'Within' setting of a "
        ."shuffle column: ({$errorOrigin}) ".$msg;
}

/**
 * [Description].
 *
 * @param array      $array     The array to search.
 * @param string|int $search    The key or index to find.
 * @param string     $errorOrig
 *
 * @return string|int The search term if it could be found.
 *
 * @throws \Exception Exception thrown if the search is out-of-bounds.
 *
 * @todo description for findIndex()
 */
function findIndex($array, $search, $errorOrig)
{
    // this requires the column headers to have been lower-cased, to correctly find "x" rather than "X"
    $search = strtolower(trim($search));
    $flip = array_change_key_case(array_flip(array_values($array)), CASE_LOWER);
    if (isset($flip[$search])) {
        return $flip[$search];
    } elseif (is_numeric($search)) {
        --$search;
        if (!isset($array[$search])) {
            $msg = getShuffleExceptionMsg('The search \''.(++$search).'\' '
                .'is bigger than the total number of columns ('.count($array)
                .').', $errorOrig);
            throw new \Exception($msg);
        }

        return $search;
    } elseif (ctype_alpha($search) && strlen($search) === 1) {
        $temp = count(range('a', $search)) - 1;
        if (!isset($array[$temp])) {
            $msg = getShuffleExceptionMsg("The search '{$search}' is not "
                .'the letter code for an existing column.', $errorOrig);
            throw new \Exception($msg);
        }

        return $temp;
    }

    $msg = getShuffleExceptionMsg("The search '{$search}' is not a literal "
        .'column header, a numeric search, or a single letter code. If you '
        ."are trying to use a shortcut for a column past 'z', please use a "
        .'numeric code instead.', $errorOrig);
    throw new \Exception($msg);
}

/**
 * Shuffles a 2-D array.
 *
 * @param array $array The array to shuffle.
 * @param bool  $debug Set true to print the array before shuffling and after.
 *
 * @return array The shuffled array
 *
 * @todo description for what specifically happens in this function.
 */
function shuffle2dArray(array $array, $debug = false)
{
    $noShuffle = array('' => true, '0' => true, 'off' => true, 'no' => true);

    if ($debug) {
        $startCopy = $array;
    }
    $showShuffles = false;

    $padding = array();
    $firstRow = current($array);
    while (!is_array($firstRow)) {
        $padding[] = array_shift($array);
        $firstRow = current($array);
    }

    $headers = array_keys($firstRow);

    $prevColumns = array();
    // go through each of the headers, and see if it contains shuffle instructions
    foreach ($headers as $header) {
        // if the column doesn't explicitly say which columns to target,
        // then we will just use the previous columns as the target
        $prevColumns[$header] = true;
        if (stripos($header, 'advshuffle') !== false) {
            $showShuffles = true;
            $shuffleInfo = explode(';', $header);
            // the type of shuffle (simple, block, or list), should be before the first semicolon
            $type = array_shift($shuffleInfo);

            $target = findSetting($shuffleInfo, 'target');
            if (is_bool($target)) {
                // if false, "target" wasn't found.  
                // if true, they had the "target" keyword, but no columns
                // either way, just shuffle the columns up to this point
                $targets = $prevColumns;
            } else {
                // targets can be specified as ranges, like "Target: 1,2,5-8,10"
                $target = explode(',', $target);
                $targets = array();
                foreach ($target as $targ) {
                    $these = explode('-', $targ);
                    $first = array_shift($these);
                    $first = findIndex($headers, $first, $header);
                    if ($these !== array()) {
                        // if they did specify a range (e.g. 1-3), $these won't be empty after array_shift
                        // if so, get all the columns in this range, and add them to $targets
                        $last = array_pop($these);
                        $last = findIndex($headers, $last, $header);
                        $targRange = range($first, $last);
                        foreach ($targRange as $t) {
                            $targets[$headers[$t]] = true;
                        }
                    } else {
                        $targets[$headers[$first]] = true;
                    }
                }
            }
            // targets should include itself, so that the shuffle column will reflect its own changes
            $targets[$header] = true;

            $within = findSetting($shuffleInfo, 'within');
            if (is_bool($within)) {
                // if they didn't specify a within column, just make a temp array
                // and pretend everything is in the same within category
                $within = array();
                foreach ($array as $i => $row) {
                    $within[$i] = '1';
                }
            } else {
                $within = findIndex($headers, $within, $header);
                $withinCol = $headers[$within];
                // add the within to the targets, so that if block shuffle
                // affects the order of the non-shuffled rows, we can still see that
                // also, we can display the within during debugging
                $targets[$withinCol] = true;
                $within = array();
                foreach ($array as $i => $row) {
                    $within[$i] = $row[$withinCol];
                }
            }

            // we just want to grab the target columns from each of the rows
            $shuffled = array();
            foreach ($array as $i => $row) {
                foreach ($targets as $t => $unused) {
                    $shuffled[$i][$t] = $row[$t];
                }
            }

            if ($debug) {
                echo '<div style="white-space: nowrap">';
                echo 'Shuffle Column: '.$header.'<br>';
                echo '<div style="display: inline-block">';
                echo 'Before shuffle:<br>';
                display2dArray($shuffled);
                echo '</div>';
            }

            if (stripos($type, 'block') !== false) {
                superBlockShuffle($shuffled, $header, $within, $noShuffle);
            } elseif (stripos($type, 'list') !== false) {
                listShuffle($shuffled, $header, $within, $noShuffle);
            } elseif (stripos($type, 'side') !== false) {
                sideShuffle($shuffled, $header, $within, $noShuffle);
            } else {
                simpleShuffle($shuffled, $header, $within, $noShuffle);
            }

            if ($debug) {
                echo '<div style="display: inline-block">';
                echo 'After shuffle:<br>';
                display2dArray($shuffled);
                echo '</div>';
                echo '</div>';
            }

            // put the shuffled result back in the array
            foreach ($shuffled as $i => $row) {
                foreach ($row as $t => $value) {
                    $array[$i][$t] = $value;
                }
            }
        }
    }

    foreach (array_reverse($padding) as $row) {
        array_unshift($array, $row);
    }

    if ($debug && $showShuffles) {
        echo '<div style="white-space: nowrap">';
        echo '<div style="display: inline-block">';
        echo 'Before all advanced shuffles:<br>';
        display2dArray($startCopy);
        echo '</div>';
        echo '<div style="display: inline-block">';
        echo 'After advanced shuffles:<br>';
        display2dArray($array);
        echo '</div>';
        echo '</div>';
    }

    return $array;
}

/**
 * any rows with the same value in the same "within" value will be shuffled together.
 * Note: Not tested with associative arrays.
 * 
 * @param array  $array     The (by-reference) 2-D array to be shuffled.
 * @param string $shuffle   The column name that is being used to guide the 
 *                          shuffle. It is expected to exist inside $array.
 * @param array  $within    Reference array with the same indices as $array
 *                          and scalar values that indicate which group the
 *                          corresponding index in $array belongs to (e.g. 
 *                          '1', '2', 'yes', 'no', '', 'banana').
 * @param array  $noShuffle Groups in $within and $shuffle that should not 
 *                          be shuffled. Generally these are set to '', 
 *                          'off', 'no', and '0'. Values can be anything 
 *                          except NULL.
 * 
 * @todo test simpleShuffle() on associative arrays.
 * @todo description of simpleShuffle() does not make sense.
 */
function simpleShuffle(array &$array, $shuffle, array $within, array $noShuffle)
{
    $indices = array();
    foreach ($array as $i => $row) {
        if (!isset($noShuffle[strtolower($within[$i])])
            && !isset($noShuffle[strtolower($row[$shuffle])])
        ) {
            $indices[$within[$i]][$row[$shuffle]][$i] = $i;
        }
    }
    foreach ($indices as $w) {
        foreach ($w as $set) {
            $old = array();
            foreach ($set as $i) {
                $old[$i] = $array[$i];
            }
            shuffleAssoc($set);
            foreach ($set as $i => $new) {
                $array[$i] = $old[$new];
            }
        }
    }
}

/**
 * BlockShuffle.
 * 
 * As long as two consecutive rows have the same value in their shuffle 
 * column and their $within setting, they will be grouped into the same 
 * block. Then, shuffled blocks will be shuffled, and the array will be 
 * reconstructed by going through each block and writing each row back into
 * the array.
 * 
 * It's possible to affect non-shuffled rows with this. If a block with two
 * rows is shuffled with a block with 4 rows, then everything under the 
 * original 2-row block will be moved down to make way for the 4-row block.
 * 
 * @param array  $array     The (by-reference) 2-D array to be shuffled.
 * @param string $shuffle   The column name that is being used to guide the 
 *                          shuffle. It is expected to exist inside $array.
 * @param array  $within    Reference array with the same indices as $array
 *                          and scalar values that indicate which group the
 *                          corresponding index in $array belongs to (e.g. 
 *                          '1', '2', 'yes', 'no', '', 'banana').
 * @param array  $noShuffle Groups in $within and $shuffle that should not 
 *                          be shuffled. Generally these are set to '', 
 *                          'off', 'no', and '0'. Values can be anything 
 *                          except NULL.
 * 
 * @todo update description for superBlockShuffle
 */
function superBlockShuffle(array &$array, $shuffle, array $within, array $noShuffle)
{
    $blocks = array();
    $blocksToShuffle = array();
    $currentBlock = -1;
    $prevWith = false;
    $prevCode = false;
    $shufflePatterns = array();
    foreach ($array as $i => $row) {
        // first, if this is a non-shuffled row, standardize this value
        if (isset($noShuffle[strtolower($within[$i])])
            ||  isset($noShuffle[strtolower($row[$shuffle])])
        ) {
            $withCode = 'off';
            $shuffleCode = 'off';
        } else {
            $withCode = $within[$i];
            $shuffleCode = $row[$shuffle];
        }

        // check to see if we are starting a new block
        if ($withCode !== $prevWith || $shuffleCode !== $prevCode) {
            ++$currentBlock;
            // if we are starting a block that doesn't need a shuffle,
            // simply have it point to itself
            // otherwise, put it in a new block, which will be shuffled
            if ($shuffleCode === 'off') {
                $shufflePatterns[$currentBlock] = $currentBlock;
            } else {
                $blocksToShuffle[$withCode][$currentBlock] = $currentBlock;
            }
            $prevCode = $shuffleCode;
            $prevWith = $withCode;
        }
        // make a list of all blocks, so we can write them back into $array at the end
        $blocks[$currentBlock][] = $i;
    }

    // shuffle around the blocks, which are pointing to themselves originally
    // e.g., array(a => a, b => b, c => c) becomes array(a => b, b => c, c => a)
    foreach ($blocksToShuffle as $blockedWithin) {
        shuffleAssoc($blockedWithin);
        foreach ($blockedWithin as $orig => $new) {
            $shufflePatterns[$orig] = $new;
        }
    }

    // foreach block, use the block that this points to in shufflePatterns
    // if its an unshuffled block, it will point to itself
    // with each of these, get each row number in that block, and write those
    // rows into the new array.  Once we are done, replace array with newArray
    $newArray = array();
    foreach ($blocks as $n => $block) {
        foreach ($blocks[$shufflePatterns[$n]] as $i) {
            $newArray[] = $array[$i];
        }
    }
    $array = $newArray;
}

/**
 * listShuffle.
 * 
 * Shuffled items are categorized into lists, and then each shuffled list is
 * pointed at another shuffled list. Then for each row, if that row belongs
 * to a shuffled list, it is replaced with the next row from the matching 
 * replacement list.
 * If one list is longer than its replacement, the replacement will start 
 * reusing rows, starting back at the first row. If the replacement is too
 * long, unused rows will simply be lost.
 * 
 * @example
 *       Cue  Target    ListShuffle          Cue  Target    ListShuffle 
 *   1.  a    apple     1                1.  b    bear      2   
 *   2.  b    bear      2                2.  a    apple     1 
 *   3.  c    cucumber  1                3.  d    dog       2
 *   4.  d    dog       2            =>  4.  c    cucumber  1
 *   5.  e    eggplant  1                5.  f    fish      2 
 *   6.  f    fish      2                6.  e    eggplant  1  
 *   7.  g    grapes    1                7.  h    horse     2
 *   8.  h    horse     2                8.  g    grapes    1 
 * 
 * @param array  $array     The (by-reference) 2-D array to be shuffled.
 * @param string $shuffle   The column name that is being used to guide the 
 *                          shuffle. It is expected to exist inside $array.
 * @param array  $within    Reference array with the same indices as $array
 *                          and scalar values that indicate which group the
 *                          corresponding index in $array belongs to (e.g. 
 *                          '1', '2', 'yes', 'no', '', 'banana').
 * @param array  $noShuffle Groups in $within and $shuffle that should not 
 *                          be shuffled. Generally these are set to '', 
 *                          'off', 'no', and '0'. Values can be anything 
 *                          except NULL.
 */
function listShuffle(array &$array, $shuffle, array $within, array $noShuffle)
{
    $newArray = array();
    $lists = array();
    // first, group row numbers into lists, group lists into withins
    foreach ($array as $i => $row) {
        if (!isset($noShuffle[strtolower($row[$shuffle])])
            && !isset($noShuffle[strtolower($within[$i])])
        ) {
            $lists[$within[$i]][$row[$shuffle]][] = $i;
        }
    }
    // shuffle the contents of each list
    foreach ($lists as &$listWithin) {
        shuffleAssoc($listWithin);
    }
    foreach ($array as $i => $row) {
        // check if the row is a shuffled one
        if (isset($lists[$within[$i]][$row[$shuffle]])) {
            // if so, get the number of the next row in the replacement list
            // once we have the row number, we can record the new line, with the current index as the key
            $index = each($lists[$within[$i]][$row[$shuffle]]);
            // if each() has reached the end of an array, use the first row in that list instead
            // also, we need to increment the array's internal pointer, since using reset() will
            // return the first value without moving the pointer to the next row
            if ($index === false) {
                $newArray[$i] = $array[reset($lists[$within[$i]][$row[$shuffle]])];
                next($lists[$within[$i]][$row[$shuffle]]);
            } else {
                $newArray[$i] = $array[$index[1]];
            }
        }
    }
    // replace each shuffled row with its new row
    foreach ($newArray as $i => $row) {
        $array[$i] = $row;
    }
}

/** 
 * Shuffles within a row, rather than between rows, excluding the shuffle 
 * column.
 * 
 * @param array  $array   The (by-reference) 2-D array to be shuffled.
 * @param string $shuffle The column name that is being used to guide the 
 *                        shuffle. It is expected to exist inside $array.
 */
function sideShuffle(&$array, $shuffle)
{
    foreach ($array as &$row) {
        $contents = $row;
        unset($contents[$shuffle]);
        shuffleAssoc($contents);
        foreach ($contents as $key => $value) {
            $row[$key] = $value;
        }
    }
}

/**
 * Recursively shuffles an array from top (highest level) to bottom.
 * To disable shuffling for an item at a given level do one of the following:
 *   - use 'off' in whichever case you'd like (e.g., 'Off', 'OFF', etc.)
 *   - include a hashtag/pound sign in the shuffle column (e.g., '#Group1').
 *
 * @param array $input  2-D data read from a .csv table using getFromFile().
 * @param int   $levels Indicates which level the function is currently 
 *                      shuffling (do not adjust --- this is for the function).
 *
 * @return array The shuffled array.
 *
 * @see getFromFile()
 */
function multiLevelShuffle($input, $levels = 0)
{
    $root = 'Shuffle ';
    $offChar = '#';
    $output = array();
    $subset = array();

    /*
     *  Initialize shuffling
     */
    if ($levels === 0) {
        // save padding, if it exists
        $padding = array();
        while ($input[0] === 0) {
            $padding[] = array_shift($input);
        }

        // Find maximum Shuffle level used: while 'Shuffle#' exists check next level of shuffling
        $checkLevel = 1;
        while (isset($input[0][$root.$checkLevel])) {
            ++$checkLevel;
        }
        $maxLevel = $checkLevel - 1;
        if ($maxLevel === 0) {
            // prepend the removed padding
            return array_merge($padding, $input);
        }

        // run this function at the highest shuffle level
        $output = multiLevelShuffle($input, (int) $maxLevel);

        // prepend the removed padding
        return array_merge($padding, $output);
    }

    /*
     *  Higher order block shuffling from max down to 2
     */
    if ($levels > 1) {
        // get what is below the current level
        $subLevel = $levels - 1;

        // starting shuffle code
        $begin = $input[0][$root.$levels];
        $size = count($input);
        for ($i = 0; $i < $size; ++$i) {
            // current shuffle code
            $current = $input[$i][$root.$levels];

            // lower shuffle code
            $currentLo = $input[$i][$root.$subLevel];

            if ((strpos($begin, $offChar) !== false)
                 || (strtolower($begin) === 'off')
                 || ($begin === '')
             ) {
                // the current shuffle code is off
                // add it to the subset if the code hasn't changed from the starting value
                if ($begin === $current) {
                    $subset[] = $input[$i];
                    continue;
                } else {
                    // the shuffle code has changed
                    $output = array_merge($output, multiLevelShuffle($subset, (int) $levels - 1));

                    // empty the subset
                    $subset = array();
                    $begin = $current;

                    // if the next code is turned off add it to the current subset
                    if ((strpos($begin, $offChar) !== false)
                         || (strtolower($begin) === 'off')
                         || ($begin === '')
                     ) {
                        $subset[] = $input[$i];
                        continue;
                    }
                }
            }
            // if the shuffle code hasn't changed (and isn't off) add it to a 
            // $holder array (grouped by lower shuffle column)
            if ($begin === $current) {
                $holder[$currentLo][] = $input[$i];
            } else {
                // the shuffle code changed (and isn't off): shuffle the lower groups
                shuffle($holder);
                $subset = array();
                foreach ($holder as $group) {
                    // add all items from the $holder to the $subset
                    foreach ($group as $item) {
                        $subset[] = $item;
                    }
                }
                $output = array_merge($output, multiLevelShuffle($subset, (int) $levels - 1));
                $subset = array();
                $begin = $current;
                $holder = array();
                if ((strpos($begin, $offChar) !== false)
                    || (strtolower($begin) === 'off')
                    || ($begin === '')
                ) {
                    // add current item to the subset if shuffle code is off
                    $subset[] = $input[$i];
                } else {
                    // add current item to the $holder if shuffle code is not off
                    $holder[$currentLo][] = $input[$i];
                }
            }
        }

        // send the final subset to be shuffled (if the file ends with an off code)
        if ($subset != array()) {
            $output = array_merge($output, multiLevelShuffle($subset, (int) $levels - 1));
            $subset = array();
        } else {
            // send the final holder to be shuffled (if the file does not end with an off code)
            shuffle($holder);
            foreach ($holder as $group) {
                foreach ($group as $item) {
                    $subset[] = $item;
                }
            }
            $output = array_merge($output, multiLevelShuffle($subset, (int) $levels - 1));
        }

        return $output;
    }

    /*
     *  Level 1 shuffle (aware of names)
     */
    if ($levels === 1) {
        // group each item by shuffle code
        $groupedItems = array();
        foreach ($input as $subArray) {
            $group = $subArray[$root.$levels];
            $groupedItems[$group][] = $subArray;
        }
        foreach ($groupedItems as $shuffleType => &$items) {
            // skip shuffling of items as long as none of the "don't shuffle" conditons are met
            if ((strpos($shuffleType, $offChar) === false)
                && (strtolower($shuffleType) !== 'off')
                && ($shuffleType !== '')
            ) {
                shuffle($items);
            }
        }
        // go through unshuffled input and pull items from the shuffled groups and put them into the output
        foreach ($input as $pos => $item) {
            $shuffleCode = $item[$root.$levels];
            $output[$pos] = array_shift($groupedItems[$shuffleCode]);
        }
        return $output;
    }
}
