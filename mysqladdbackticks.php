<?php

/*
    PRODUCT NAME: MySqliAddBacktick

    REQUIREMENTS: PHP 5.x. It doesn't work with PHP 7.x.

    AUTHOR: FRANCESCO TORRE

    DESCRIPTION:

    This is utility software written in PHP for MySql.

    It is important to surround field/table names wiht backticks to avoid conflicts with reserved keywords, 
    but Italian keyboard do not have a backtick key. 

    This program allows for inputting a query, and automatically output it with backticks placed where 
    needed.

    For example, given the following input: 

            select employee.firstname, employee.lastname from users where employee.id between 30 and 50

    it returns as output (when beautify is on):

            SELECT 
                `employee` . `firstname`,
                `employee` . `lastname` 
            FROM 
                `users` 
            WHERE 
                `employee` .`id` BETWEEN 30 AND 50
    
    I haven't fully tested the software with complex queries (sub-selects, unions, etc.), but it seems to work 
    pretty well for common cases.

    Standard statements SELECT, INSERT, UPDATE and DELETE are fully supported.

    FEATURES:

    - Supports the most common MySql reserved keywords

    - Supports using reserved keywords as table/field names

    - Distinguishes between reserved keywords in expressions and string values

    - Basic output beautification

    SECURITY:

    - Built-in security against SQL Injection.

    NOTES:

    - In some cases, I have implemented a very complex logic to achieve the desired
      result. One could argue that there is a simpler way to achieve the same result.
      The fact is, I challenged myself to write code in such a way a developer have
      the opportunity (to a certain degree) to name tables and fields using reserved 
      keywords, if he so wishes (i.e. table/field `into`, `select`, etc.). MySql allows 
      you to do that, but requires those names to be enclosed in backticks.

*/
?>

<?php
    /*
        *********************
        CUSTOMIZABLE SETTINGS
        *********************
    */

    //Disable/enable showing errors on the page
    ini_set ('display_errors', '0');

    //In order to validate, a successful connection to MySql is required (see settings below)
    $validate = true;

    //Connection credentials to validate query syntax against a test database
    $conn_info = array(
        'server' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'db' => 'dbname',
    );

    $beautify = true;

    $remove_comments = true;

    //Allow for many statements (semicolon-separated) to be validated and parsed. 
    //If true, could increase risk of SQL Injection.
    $allow_multi_statement = false;

    /*
        *************************
        END CUSTOMIZABLE SETTINGS
        *************************
    */  
?>

<?php

    $exception_result = array('errno' => 0, 'error' => '', 'success' => true, 'error type' => '');

    $datatypes_no_newlines = array(
        'BIGINT',
        'BIT',
        'BLOB',
        'BOOLEAN',
        'CHAR',
        'DATE',
        'DATETIME',
        'DECIMAL',
        'DOUBLE',
        'ENUM',
        'FLOAT',
        'INT',
        'LONGBLOB',
        'LONGTEXT',
        'MEDIUMBLOB',
        'MEDIUMINT',
        'MEDIUMTEXT',
        'SET',
        'SMALLINT',
        'TEXT',
        'TIME',
        'TIMESTAMP',
        'TINYINT',
        'TINYTEXT',
        'VARCHAR'
    );

    $reserved_no_newlines = array(

        //Originally found in $newline_before_and_after
        'AND',
        'OR',
        'XOR',

        //Standard entries in $reserved_no_newlines
        'ACCESSIBLE',
        'ACTION',
        'AGAINST',
        'AGGREGATE',
        'ALGORITHM',
        'ALL',
        'ALTER',
        'ANALYSE',
        'ANALYZE',
        'AS',
        'ASC',
        'AUTOCOMMIT',
        'AUTO_INCREMENT',
        'BACKUP',
        'BEGIN',
        'BETWEEN',
        'BINLOG',
        'BOTH',
        'CASCADE',
        'CASE',
        'CHANGE',
        'CHANGED',
        'CHARACTER SET',
        'CHARSET',
        'CHECK',
        'CHECKSUM',
        'COLLATE',
        'COLLATION',
        'COLUMN',
        'COLUMNS',
        'COMMENT',
        'COMMIT',
        'COMMITTED',
        'COMPRESSED',
        'CONCURRENT',
        'CONSTRAINT',
        'CONTAINS',
        'CONVERT',
        'CREATE',
        'CROSS',
        'CURRENT_TIMESTAMP',
        'DATABASE',
        'DATABASES',
        'DAY',
        'DAY_HOUR',
        'DAY_MINUTE',
        'DAY_SECOND',
        'DEFAULT',
        'DEFINER',
        'DELAYED',
        'DELETE',
        'DESC',
        'DESCRIBE',
        'DETERMINISTIC',
        'DISTINCT',
        'DISTINCTROW',
        'DIV',
        'DO',
        'DUMPFILE',
        'DUPLICATE',
        'DYNAMIC',
        'ELSE',
        'ENCLOSED',
        'END',
        'ENGINE',
        'ENGINES',
        'ENGINE_TYPE',
        'ESCAPE',
        'ESCAPED',
        'EVENTS',
        'EXEC',
        'EXECUTE',
        'EXISTS',
        'EXPLAIN',
        'EXTENDED',
        'FAST',
        'FIELDS',
        'FILE',
        'FIRST',
        'FIXED',
        'FLUSH',
        'FOR',
        'FORCE',
        'FOREIGN',
        'FULL',
        'FULLTEXT',
        'FUNCTION',
        'GLOBAL',
        'GRANT',
        'GRANTS',
        'GROUP_CONCAT',
        'HEAP',
        'HIGH_PRIORITY',
        'HOSTS',
        'HOUR',
        'HOUR_MINUTE',
        'HOUR_SECOND',
        'IDENTIFIED',
        'IF',
        'IFNULL',
        'IGNORE',
        'IN',
        'INDEX',
        'INDEXES',
        'INFILE',
        'INSERT',
        'INSERT_ID',
        'INSERT_METHOD',
        'INTERVAL',
        'INTO',
        'INVOKER',
        'IS',
        'ISOLATION',
        'KEY',
        'KEYS',
        'KILL',
        'LAST_INSERT_ID',
        'LEADING',
        'LEVEL',
        'LIKE',
        'LINEAR',
        'LINES',
        'LOAD',
        'LOCAL',
        'LOCK',
        'LOCKS',
        'LOGS',
        'LOW_PRIORITY',
        'MARIA',
        'MASTER',
        'MASTER_CONNECT_RETRY',
        'MASTER_HOST',
        'MASTER_LOG_FILE',
        'MATCH',
        'MAX_CONNECTIONS_PER_HOUR',
        'MAX_QUERIES_PER_HOUR',
        'MAX_ROWS',
        'MAX_UPDATES_PER_HOUR',
        'MAX_USER_CONNECTIONS',
        'MEDIUM',
        'MERGE',
        'MINUTE',
        'MINUTE_SECOND',
        'MIN_ROWS',
        'MODE',
        'MODIFY',
        'MONTH',
        'MRG_MYISAM',
        'MYISAM',
        'NAMES',
        'NATURAL',
        'NOT',
        'NOW',
        'NULL',
        'OFFSET',
        'ON',
        'ON DELETE',
        'ON UPDATE',
        'OPEN',
        'OPTIMIZE',
        'OPTION',
        'OPTIONALLY',
        'OUTFILE',
        'PACK_KEYS',
        'PAGE',
        'PARTIAL',
        'PARTITION',
        'PARTITIONS',
        'PASSWORD',
        'PRIMARY',
        'PRIVILEGES',
        'PROCEDURE',
        'PROCESS',
        'PROCESSLIST',
        'PURGE',
        'QUICK',
        'RAID0',
        'RAID_CHUNKS',
        'RAID_CHUNKSIZE',
        'RAID_TYPE',
        'RANGE',
        'READ',
        'READ_ONLY',
        'READ_WRITE',
        'REFERENCES',
        'REGEXP',
        'RELOAD',
        'RENAME',
        'REPAIR',
        'REPEATABLE',
        'REPLACE',
        'REPLICATION',
        'RESET',
        'RESTORE',
        'RESTRICT',
        'RETURN',
        'RETURNS',
        'REVOKE',
        'RLIKE',
        'ROLLBACK',
        'ROW',
        'ROWS',
        'ROW_FORMAT',
        'SECOND',
        'SECURITY',
        'SEPARATOR',
        'SERIALIZABLE',
        'SESSION',
        'SHARE',
        'SHOW',
        'SHUTDOWN',
        'SLAVE',
        'SONAME',
        'SOUNDS',
        'SQL',
        'SQL_AUTO_IS_NULL',
        'SQL_BIG_RESULT',
        'SQL_BIG_SELECTS',
        'SQL_BIG_TABLES',
        'SQL_BUFFER_RESULT',
        'SQL_CACHE',
        'SQL_CALC_FOUND_ROWS',
        'SQL_LOG_BIN',
        'SQL_LOG_OFF',
        'SQL_LOG_UPDATE',
        'SQL_LOW_PRIORITY_UPDATES',
        'SQL_MAX_JOIN_SIZE',
        'SQL_NO_CACHE',
        'SQL_QUOTE_SHOW_CREATE',
        'SQL_SAFE_UPDATES',
        'SQL_SELECT_LIMIT',
        'SQL_SLAVE_SKIP_COUNTER',
        'SQL_SMALL_RESULT',
        'SQL_WARNINGS',
        'START',
        'STARTING',
        'STATUS',
        'STOP',
        'STORAGE',
        'STRAIGHT_JOIN',
        'STRING',
        'STRIPED',
        'SUPER',
        'TABLE',
        'TABLES',
        'TEMPORARY',
        'TERMINATED',
        'THEN',
        'TO',
        'TRAILING',
        'TRANSACTIONAL',
        'TRUE',
        'TRUNCATE',
        'TYPE',
        'TYPES',
        'UNCOMMITTED',
        'UNIQUE',
        'UNLOCK',
        'UNSIGNED',
        'USAGE',
        'USE',
        'USING',
        'VARIABLES',
        'VIEW',
        'WHEN',
        'WITH',
        'WORK',
        'WRITE',
        'YEAR_MONTH'
    );

    $newline_before = array(
        'ADD',
        'AFTER',
        'ALTER TABLE',
        'DELETE FROM',
        'DROP',
        // 'EXCEPT',
        // 'FROM',
        // 'GROUP BY',
        // 'HAVING',
        // 'INTERSECT',
        'LIMIT',
        // 'ORDER BY',
        // 'SELECT',
        'SET',
        // 'UNION',
        // 'UNION ALL',
        'UPDATE',
        'VALUES',
        // 'WHERE'
    );

    $newline_before_and_after = array(

        //Originally found in newline_before
        'EXCEPT',
        'FROM',
        'GROUP BY',
        'HAVING',
        'INTERSECT',
        'ORDER BY',
        'SELECT',
        'UNION',
        'UNION ALL',
        'WHERE',
        
        //Standard entries in $newline_before_and_after
        // 'AND',
        'INNER JOIN',
        'LEFT JOIN',
        'LEFT OUTER JOIN',
        // 'OR',
        'RIGHT JOIN',
        'RIGHT OUTER JOIN',
        'OUTER JOIN',
        'JOIN'
        // ,'XOR'
    );

    $functions_no_newlines = array(
        'ABS',
        'ACOS',
        'ADDDATE',
        'ADDTIME',
        'AES_DECRYPT',
        'AES_ENCRYPT',
        'AREA',
        'ASBINARY',
        'ASCII',
        'ASIN',
        'ASTEXT',
        'ATAN',
        'ATAN2',
        'AVG',
        'BDMPOLYFROMTEXT',
        'BDMPOLYFROMWKB',
        'BDPOLYFROMTEXT',
        'BDPOLYFROMWKB',
        'BENCHMARK',
        'BIN',
        'BIT_AND',
        'BIT_COUNT',
        'BIT_LENGTH',
        'BIT_OR',
        'BIT_XOR',
        'BOUNDARY',
        'BUFFER',
        'CAST',
        'CEIL',
        'CEILING',
        'CENTROID',
        'CHAR',
        'CHARACTER_LENGTH',
        'CHARSET',
        'CHAR_LENGTH',
        'COALESCE',
        'COERCIBILITY',
        'COLLATION',
        'COMPRESS',
        'CONCAT',
        'CONCAT_WS',
        'CONNECTION_ID',
        'CONTAINS',
        'CONV',
        'CONVERT',
        'CONVERT_TZ',
        'CONVEXHULL',
        'COS',
        'COT',
        'COUNT',
        'CRC32',
        'CROSSES',
        'CURDATE',
        'CURRENT_DATE',
        'CURRENT_TIME',
        'CURRENT_TIMESTAMP',
        'CURRENT_USER',
        'CURTIME',
        'DATABASE',
        'DATE',
        'DATEDIFF',
        'DATE_ADD',
        'DATE_DIFF',
        'DATE_FORMAT',
        'DATE_SUB',
        'DAY',
        'DAYNAME',
        'DAYOFMONTH',
        'DAYOFWEEK',
        'DAYOFYEAR',
        'DECODE',
        'DEFAULT',
        'DEGREES',
        'DES_DECRYPT',
        'DES_ENCRYPT',
        'DIFFERENCE',
        'DIMENSION',
        'DISJOINT',
        'DISTANCE',
        'ELT',
        'ENCODE',
        'ENCRYPT',
        'ENDPOINT',
        'ENVELOPE',
        'EQUALS',
        'EXP',
        'EXPORT_SET',
        'EXTERIORRING',
        'EXTRACT',
        'EXTRACTVALUE',
        'FIELD',
        'FIND_IN_SET',
        'FLOOR',
        'FORMAT',
        'FOUND_ROWS',
        'FROM_DAYS',
        'FROM_UNIXTIME',
        'GEOMCOLLFROMTEXT',
        'GEOMCOLLFROMWKB',
        'GEOMETRYCOLLECTION',
        'GEOMETRYCOLLECTIONFROMTEXT',
        'GEOMETRYCOLLECTIONFROMWKB',
        'GEOMETRYFROMTEXT',
        'GEOMETRYFROMWKB',
        'GEOMETRYN',
        'GEOMETRYTYPE',
        'GEOMFROMTEXT',
        'GEOMFROMWKB',
        'GET_FORMAT',
        'GET_LOCK',
        'GLENGTH',
        'GREATEST',
        'GROUP_CONCAT',
        'GROUP_UNIQUE_USERS',
        'HEX',
        'HOUR',
        'IF',
        'IFNULL',
        'INET_ATON',
        'INET_NTOA',
        'INSERT',
        'INSTR',
        'INTERIORRINGN',
        'INTERSECTION',
        'INTERSECTS',
        'INTERVAL',
        'ISCLOSED',
        'ISEMPTY',
        'ISNULL',
        'ISRING',
        'ISSIMPLE',
        'IS_FREE_LOCK',
        'IS_USED_LOCK',
        'LAST_DAY',
        'LAST_INSERT_ID',
        'LCASE',
        'LEAST',
        'LEFT',
        'LENGTH',
        'LINEFROMTEXT',
        'LINEFROMWKB',
        'LINESTRING',
        'LINESTRINGFROMTEXT',
        'LINESTRINGFROMWKB',
        'LN',
        'LOAD_FILE',
        'LOCALTIME',
        'LOCALTIMESTAMP',
        'LOCATE',
        'LOG',
        'LOG10',
        'LOG2',
        'LOWER',
        'LPAD',
        'LTRIM',
        'MAKEDATE',
        'MAKETIME',
        'MAKE_SET',
        'MASTER_POS_WAIT',
        'MAX',
        'MBRCONTAINS',
        'MBRDISJOINT',
        'MBREQUAL',
        'MBRINTERSECTS',
        'MBROVERLAPS',
        'MBRTOUCHES',
        'MBRWITHIN',
        'MD5',
        'MICROSECOND',
        'MID',
        'MIN',
        'MINUTE',
        'MLINEFROMTEXT',
        'MLINEFROMWKB',
        'MOD',
        'MONTH',
        'MONTHNAME',
        'MPOINTFROMTEXT',
        'MPOINTFROMWKB',
        'MPOLYFROMTEXT',
        'MPOLYFROMWKB',
        'MULTILINESTRING',
        'MULTILINESTRINGFROMTEXT',
        'MULTILINESTRINGFROMWKB',
        'MULTIPOINT',
        'MULTIPOINTFROMTEXT',
        'MULTIPOINTFROMWKB',
        'MULTIPOLYGON',
        'MULTIPOLYGONFROMTEXT',
        'MULTIPOLYGONFROMWKB',
        'NAME_CONST',
        'NULLIF',
        'NUMGEOMETRIES',
        'NUMINTERIORRINGS',
        'NUMPOINTS',
        'OCT',
        'OCTET_LENGTH',
        'OLD_PASSWORD',
        'ORD',
        'OVERLAPS',
        'PASSWORD',
        'PERIOD_ADD',
        'PERIOD_DIFF',
        'PI',
        'POINT',
        'POINTFROMTEXT',
        'POINTFROMWKB',
        'POINTN',
        'POINTONSURFACE',
        'POLYFROMTEXT',
        'POLYFROMWKB',
        'POLYGON',
        'POLYGONFROMTEXT',
        'POLYGONFROMWKB',
        'POSITION',
        'POW',
        'POWER',
        'QUARTER',
        'QUOTE',
        'RADIANS',
        'RAND',
        'RELATED',
        'RELEASE_LOCK',
        'REPEAT',
        'REPLACE',
        'REVERSE',
        'RIGHT',
        'ROUND',
        'ROW_COUNT',
        'RPAD',
        'RTRIM',
        'SCHEMA',
        'SECOND',
        'SEC_TO_TIME',
        'SESSION_USER',
        'SHA',
        'SHA1',
        'SIGN',
        'SIN',
        'SLEEP',
        'SOUNDEX',
        'SPACE',
        'SQRT',
        'SRID',
        'STARTPOINT',
        'STD',
        'STDDEV',
        'STDDEV_POP',
        'STDDEV_SAMP',
        'STRCMP',
        'STR_TO_DATE',
        'SUBDATE',
        'SUBSTR',
        'SUBSTRING',
        'SUBSTRING_INDEX',
        'SUBTIME',
        'SUM',
        'SYMDIFFERENCE',
        'SYSDATE',
        'SYSTEM_USER',
        'TAN',
        'TIME',
        'TIMEDIFF',
        'TIMESTAMP',
        'TIMESTAMPADD',
        'TIMESTAMPDIFF',
        'TIME_FORMAT',
        'TIME_TO_SEC',
        'TOUCHES',
        'TO_DAYS',
        'TRIM',
        'TRUNCATE',
        'UCASE',
        'UNCOMPRESS',
        'UNCOMPRESSED_LENGTH',
        'UNHEX',
        'UNIQUE_USERS',
        'UNIX_TIMESTAMP',
        'UPDATEXML',
        'UPPER',
        'USER',
        'UTC_DATE',
        'UTC_TIME',
        'UTC_TIMESTAMP',
        'UUID',
        'VARIANCE',
        'VAR_POP',
        'VAR_SAMP',
        'VERSION',
        'WEEK',
        'WEEKDAY',
        'WEEKOFYEAR',
        'WITHIN',
        'X',
        'Y',
        'YEAR',
        'YEARWEEK'
    );

    $keywords = array();
    $keywords['reserved_no_newlines'] = &$reserved_no_newlines;
    $keywords['newline_before'] = &$newline_before;
    $keywords['newline_before_and_after'] = &$newline_before_and_after;
    $keywords['functions_no_newlines'] = &$functions_no_newlines;
    $keywords['datatypes_no_newlines'] = &$datatypes_no_newlines;

    $query_before = '';
    $query_after = '';
    $msg_tag = '';

    $beautify_on = $beautify ? 'checked' : '';

    $validate_on = $validate ? 'checked' : '';

    $input_textarea_style = 'rgb(128, 120, 120)';

    if (count($_POST) > 0) {

        if (!isset($_POST['chkBeautify'])) {

            $beautify_on = '';
            $beautify = false;
        }
        else {
            $beautify_on = 'checked';
            $beautify = true;
        }

        if (!isset($_POST['chkValidate'])) {

            $validate_on = '';
            $validate = false;
        }else {
            $validate_on = 'checked';
            $validate = true;
        }

    }
    
    if (isset($_POST['query'])) {
        
        if (!empty($_POST['query'])) {
            
            $query = trim($_POST['query']);
            $query_before = $query; 
            $input_textarea_style = 'black';

            $exception = false;
           
            if ($validate) {
                
                //Four stpes to handle exceptions
                //
                //1. Make a call to mysqli_query_validate(). It returns true or false,
                //   depending on whether validation, respectively, succeeded or not (not
                //   used here)
                mysqli_query_validate($conn_info, $query, $allow_multi_statement);

                //2. global $exception_result is populated with the exception details (if any). Use it
                //   to set label with error messages, execute conditional code, ignore some errors, etc.
                handle_exceptions();
                
                //3. After the exception object has been manipulated (if there are ignored errors, 'success' 
                //   property is set to true), store the result in a variable
                $exception = !$exception_result['success'];
                
                //4. Reset global $exception_result
                reset_exception_result();   
            }
            
            //4. Use the exception outcome
            if ( !$exception ) {
                $query_after = mysqli_add_backticks($query, $beautify, $remove_comments);
            } 
        }
        else {
            $msg_tag = '<span class="msg" style="color:red;font-size:16px">Please, enter a SQL query.</span>';
        }
    }

    function mysqli_add_backticks($string_query, $beautify=true, $bool_remove_comments = false) {  
         
        //Remove redundant whithespaces (leave only single ones)
        $string_query = preg_replace('/[\s\p{Zs}]{2,}/m', ' ', $string_query);

        $array_query = query_filter($string_query, $bool_remove_comments);

        $array_query = query_flatten_array($array_query);

        $array_query = query_add_backticks($array_query);

        $string_query = query_apply_formatting($array_query, $beautify);

        return $string_query;
    }

    //Return string
    function mysqli_query_comments_remove($string_query){

        if (mysqli_query_comments_found($string_query)) {
            
            $array_symbols = array('#', '--', '/*', '/ *', '*/');
        
            foreach ($array_symbols as $symbol) {
               
                $string_query = str_replace($symbol, '', $string_query);
                
            }
        }

        return $string_query;
    }

    function mysqli_query_comments_found($string_query){
        
        $array_symbols = array('#', '--', '/*', '/ *', '*/');
        
        for ($i=0; $i < count($array_symbols)-1  ; $i++) { 

            $symbol = $array_symbols[$i];

            if (strpos($string_query,$symbol)!==false) {
                return true;
            }
        }

        return false;
    }

    //Returns array
    function mysqli_query_validate($array_conn_info, $string_query, $bool_allow_multi = false){
        
        global $exception_result;

        if (empty($array_conn_info)) {

            $exception_result['errno'] = '-1';
            $exception_result['error'] = 'Could not connect to database. Array containing connection info is empty.';
            $exception_result['success'] = false;
            $exception_result['error type'] = 'connection';

            return $exception_result['success'];
        }
        
        $conn = @mysqli_connect($array_conn_info['server'], 
                                $array_conn_info['username'],  
                                $array_conn_info['password'], 
                                $array_conn_info['db']);

        if (!$conn){

            $exception_result['errno'] = mysqli_connect_errno(); //1045

            $exception_result['error'] = mysqli_connect_error(); //Access denied for user '<username>'@'localhost' 
                                                                 //(using password: YES)

            $exception_result['success'] = false;
            $exception_result['error type'] = 'connection';

            return $exception_result['success'];
        }

        if (trim($string_query)) {

            /**
             * All keywords but 'REPLACE' are 6 characters long. Because the only reserved keyword that
             * include 'REPLAC' is 'REPLACE', I am going to use 'REPLAC' for easy of coding.
             */
            $supported_statements = array('SELECT', 'DELETE', 'INSERT', 'REPLAC', 'UPDATE');

            //The statement of the query currently executing
            $statement = strtoupper(substr($string_query, 0, 6));
            
            if (!in_array($statement, $supported_statements)) {
                
                $exception_result['errno'] = '-2';

                $exception_result['error'] = 'Could not validate query. Can validate only SELECT, 
                    DELETE, INSERT, REPLACE, and UPDATE.';

                $exception_result['success'] = false;
                $exception_result['error type'] = 'parsing';

                return $exception_result['success'];
            }

            $array_query = query_parse_strings($string_query);

            /**
             * We separate multiple statements by breaking on the ';' char. However, values
             * too may contain that char.
             * 
             * To avoid breaking up string values, replace ';' in values with a custom token.
             */
            $token = '^^n2Ke-J06nuZ1NeaN2sn06n^^';

            //i.e. update users set bio = '<script>alert(1);</script>' where username = 'alex'

            for ($k=1; $k < count($array_query); $k+=2) { 
                $array_query[$k] = str_replace(';', $token, $array_query[$k]);
            }

            $string_query = implode('', $array_query);

            $string_query = "EXPLAIN " .
                preg_replace(Array(
                                    "/;\s*;/", // Remove empty statements
                                    "/;\s*$/", // Remove last ";"
                                    "/;/" // Put EXPLAIN in front of every MySql statement (separated by ";") 
                                ),
                        Array(";","", "; EXPLAIN "), $string_query);

               
            $array_query = explode(';', $string_query);
                
            $int_query_counter = 0;

            foreach ($array_query as $value) {

                if (!empty($value)) {

                    $int_query_counter++;
                }
            }

            if ( ($int_query_counter > 1) && !$bool_allow_multi) {
                
                $exception_result['errno'] = '-3';
                $exception_result['error'] = 'Multiple statements are not allowed.';
                $exception_result['success'] = false;
                $exception_result['error type'] = 'parsing';

                return $exception_result['success'];
            }

            /**
             * Restore ';' in array values
             */
            for ($k=0; $k < count($array_query); $k++) { 
                $array_query[$k] = str_replace($token, ';', $array_query[$k]);
            }
            
            foreach ($array_query as $query) {

                $mixed_result = mysqli_query($conn, $query);

                if (!$mixed_result) {
                    
                    $exception_result['errno'] = mysqli_errno($conn); //1064: You have an error in your SQL syntax

                    $exception_result['error'] = mysqli_error($conn); //You have an error in your SQL syntax; 
                                                                    //check the manual that corresponds to your 
                                                                    //MySQL server version for the 
                                                                    // right syntax to use near <statement>

                    
                    /**
                    * Sometimes it issues an exception, but "errno" is set to zero, and "error" 
                    * is set to an empty string.
                    * 
                    * It is safe to assume that, when that happens, syntax is correct.
                    */
                    if ( ($exception_result['errno'] == 0) && empty($exception_result['error']) ) {
                        $exception_result['success'] = true;
                        $exception_result['error type'] = '';
                    }
                    else {
                        $exception_result['success'] = false;
                        $exception_result['error type'] = 'query';
                    }
                    
                    
                }

                if (!$mixed_result && !$exception_result['errno'] ) {
                    
                    $exception_result['success'] = false;
                    $exception_result['errno'] = '-4';
                    $exception_result['error'] = 'Unknown SQL error';
                    $exception_result['error type'] = 'unknown';

                }
            }
         }     
         
         return $exception_result['success'];
    }

    function space($num_of_spaces, $html = false){

        $spaces = '';
        $space = $html ? '&nbsp;' :  ' ';

        for ($i=1; $i <= $num_of_spaces ; $i++) { 
            $spaces .= $space;
        }

        return $spaces;
    }

    function avoid_keyword_conflict($keyword, $conflict_prevention_token){   
        if (!defined('TOKEN')) {

            define('TOKEN', $conflict_prevention_token);
        }
        
        /*
            Whithout next line, let's assume the following is encountered:

                "left outer join"

            sql_format_text() capitalizes it and turns it into:

                "LEFT OUTER JOIN\n"

            When OUTER JOIN is encountered, this is the result:

                "LEFT OUTER\nJOIN"

            when printed, displays:

                LEFT OUTER
                JOIN

            to prevent it, we add a token (passed as argument to the function) in place of whitespaces.

            Assuming $conflict_prevention_token is set to '@@_@*', it will result in:

                @@_@*LEFT@@_@*OUTER@@_@*JOIN@@_@*

            because search is done for the whole word, LEFT OUTER wont'match. 
        */
        return implode(explode(' ', $keyword), TOKEN);

        //Token will have to be removed before using the string where the replacement took place.
    }

    function query_filter($query, $bool_remove_comments = false){
       
        //String values are the odd elements of the array
        //Even elements contains all the other query parts (i.e. 
        //keywords. Such as: 'SELECT', 'FROM', etc.)
        $query_arr = query_parse_strings($query, $bool_remove_comments);

        //Put spaces around comparison operators, parenthesis and other punctuation
        query_parse_symbols_and_whitespaces($query_arr);

        //Extract just non-string values (that is: SQL keywords, etc.). Trim them and
        //separate them
        //I.e.     [0] => 'SELECT     * FROM      users' 
        //becomes: [0] => Array('SELECT', '*', 'FROM',  'users');
        /*
         * NOTE:
         * 
         * array_filter() preserves keys (indexes).
         * 
         * We don't want that.
         * 
         * We want remove empty values, and return a new array starting at zero.
         */
        $query_arr = array_values(array_filter($query_arr, 'query_array_string_to_array', ARRAY_FILTER_USE_BOTH));

        return $query_arr;
    }

    //Depends on query_trim()
    //Must be used solely as callback with array_filter();
    function query_array_string_to_array(&$value, $key){
        if ($key%2==0) {
            $value = explode(' ', $value);

            /*
            * NOTE:
            * 
            * array_filter() preserves keys (indexes).
            * 
            * We don't want that.
            * 
            * We want remove empty values, and return a new array starting at zero.
            */
            $value = array_values(array_filter($value, 'query_trim', ARRAY_FILTER_USE_BOTH));
        }

        return true;
    }

    function query_flatten_array($input_arr) {
        $result_arr = array();

        array_walk_recursive($input_arr, function($a) use (&$result_arr) { $result_arr[] = $a; });
        return $result_arr;
    }

    //Must be used solely as callback with array_filter();
    function query_trim(&$item, $key){
        $item = trim($item);

        if($item == '')
        {
            return false;
        }

        return true;
    }

    /*     
        Requires an array in the same format as returned by query_parse_strings().
        That is: even keys contain structural parts of the query, while odd 
        keys always contain string values.
        
        Add spaces before and after the following comparison operators:
        
        '!=', '<=', '>=', '=', '<>', '<', '>'
        
        The result is that when query_parse_numbers() will be called on $query_arr,
        each parenthesis will be put in a new array as a single character (not 
        followed/preceeded by anything) 
    */
    function query_parse_symbols_and_whitespaces(&$query_arr){
        
        //REMEMBER: 
        //Even keys always contain structural parts 
        //Odd keys always contain string values
        for ($i=0; $i < count($query_arr); $i+=2) { 

            $subject = $query_arr[$i];
           
            query_normalize($subject);

            $query_arr[$i] = $subject;
        }
    }

    /*     
        Requires an array in the same format as returned by query_parse_strings()
        That is: even keys contain structural parts of the query, while odd 
        keys always contain string values 
    */
    function query_parse_numbers($query_arr){

        $result_arr = [];
        $symbols = array('!=', '<=', '>=', '=', '<>', '<', '>');

        //REMEMBER: 
        //Even keys always contain structural query parts ("SELECT * FROM...")
        //Odd keys always contain string values ("Anne Kowalsky")
        for ($i=0; $i < count($query_arr); $i++) { 
            
            /*
            * NOTE:
            * 
            * array_filter() preserves keys (indexes).
            * 
            * We don't want that.
            * 
            * We want remove empty values, and return a new array starting at zero.
            */
            $temp_arr = array_values(array_filter(explode(' ', $query_arr[$i])));

            foreach ($temp_arr as $item) {

                $item_len = strlen($item);

                for ($j=0; $j < count($symbols) ; $j++) { 

                    $symbol = $symbols[$j]  ;

                    if ( ($item_len > 1) && (strpos($item, $symbol) !== false) ) {
                        
                        $i = count($query_arr);

                        $pos = strpos($item, $symbol);
                        
                        $item = explode($symbol, $item);
                        
                        switch ($pos) {

                            case 0:
                                $result_arr[] = $symbol;
                                $result_arr[] = $item[1];
                                break;

                            case $item_len-1:
                                $result_arr[] = $symbol;
                                $result_arr[] = $item[0];
                                break;
                                                       
                            default:
                                $result_arr[] = $item[0];
                                $result_arr[] = $symbol;
                                $result_arr[] = $item[1];
                                break;
                        }

                    } else {

                        $result_arr[] = $item;

                    }
                    
                }
                
                echo '<br><br><br>';
            }


        }
    }

    //Separates string values from everything else
    function query_parse_strings($query, $bool_remove_comments = false){
        
        //Only accepted delimiters are: ' and: ".
        //If there are NOT such delimiters...
        if ( (strpos($query, '\'') === false) && (strpos($query, '"') === false) ) {
            
            if ($bool_remove_comments) {         
                
                if (mysqli_query_comments_found($query)) {

                    $query = mysqli_query_comments_remove($query);
                }
                    
            }

            return array($query);
        }

        $delimiter = '';
        $prev_char = '';
        $delimiter_indexes = [];

        //Record the position each delimiter is found at
        for ($i=0; $i < strlen($query); $i++) { 
            
            $char = $query[$i];

            if ( ($char == '\'') || ($char == '"') ) {
                
                //If it's a open delimiter...
                if (empty($delimiter)) {
                    $delimiter =  $char;
                    $delimiter_indexes[] = $i;
                }
                else {
                    if (is_close_delimiter($char, $delimiter, $prev_char)) {
                        $delimiter = '';
                        $delimiter_indexes[] = $i;
                    }
                }
            }

            $prev_char = $char;

            //If at the end of the "for" there is an unclosed starting delimiter,
            //throw an exception
            if ( ($i == count($query)-1) && !empty($delimiter) ) {
                die('FATAL ERROR: invalid query syntax');
            }
        }

        //At the end of previous 'for', $delimiter_indexes should contain
        //all delimiter positions

        //Even keys are going to contain structural part of the query 
        //Odd keys are going to contain string values
        $result_arr = [];

        $prev_delimiter_pos = 0;
        $include_delimiter = false;
        
        for ($i=0; $i < count($delimiter_indexes); $i++) { 

            //Even keys must contain string values enclosed in delimiters
            $include_delimiter = ($i%2 == 1);

            $delimiter_length = ($delimiter_indexes[$i] - $prev_delimiter_pos)+1;

            if ($prev_delimiter_pos !=0) {
                
                if (!$include_delimiter) {
                    ++$prev_delimiter_pos;  
                    --$delimiter_length;
                }
            }

            $delimiter_length = $include_delimiter ? $delimiter_length : --$delimiter_length;
            
                        
            $result_arr[$i] = substr($query, $prev_delimiter_pos, $delimiter_length);

            
            $prev_delimiter_pos = $delimiter_indexes[$i];   
        }
        
        $last_index = count($delimiter_indexes)-1;

        //Add everything else after last delimiter found
        if ( $delimiter_indexes[$last_index] < (strlen($query)-1) ) {
            
            $result_arr[$i] = substr($query, 
                                    ++$delimiter_indexes[$last_index]); // "++" skips the delimiter  
        }

        if ($bool_remove_comments) {
            
            for ($k=0; $k < count($result_arr)-1; $k+=2) { 
                
                $value = $result_arr[$k];

                if (mysqli_query_comments_found($value)) {

                    mysqli_query_comments_remove($value);
                    $result_arr[$k] = $value;

                }
                
            }

        }

        return $result_arr;
    }

    //Convert spaces to &nbsp;
    function format_string_for_web_page($string){
        return str_replace(' ', '&nbsp;', $string);
    }

    function is_close_delimiter($char, $delimiter, $prev_char){

        //******** CLOSE DELIMITER ENCOUNTERED ********
        //When a delimiter is encountered and: 
        //
        //      $delimiter is not empty
        //      $delimiter == the delimiter
        //      $prev_char is not an '\' escape character
        return !empty($delimiter) && ($char == $delimiter) && ($prev_char != '\\');
    }

    function is_punctuation(&$keyword){
        $punctuation = array('(', ')', '.');
        return in_array($keyword, $punctuation);
    }

    function is_comma(&$keyword){
        return $keyword == ',';
    }

    function is_empty(&$keyword){
        $values = array('\'\'', '""');
        return in_array($keyword, $values);
    }

    function is_datatype($keyword, &$index, &$array){
        
        $keyword = strtoupper($keyword);

        $datatypes = array('BIGINT', 'BLOB', 'BOOLEAN', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'DOUBLE', 
                           'ENUM', 'FLOAT', 'INT', 'LONGBLOB', 'LONGTEXT', 'MEDIUMBLOB', 'MEDIUMINT', 
                           'MEDIUMTEXT', 'SET', 'SMALLINT', 'TEXT', 'TIME', 'TIMESTAMP', 'TINYINT', 
                           'TINYTEXT', 'VARCHAR');

        if (!in_array($keyword, $datatypes)) return false;

       $previous_index = $index-1;
       $next_index = $index +1;
       $arr_len = count($array);

       if ( ($next_index < $arr_len) && ($previous_index >= 0) ) {

           $commands = array('CREATE', 'ALTER');
           $context_keywords = array('AUTO_INCREMENT', 'DEFAULT', 'NOT', 'NULL');

           $command = strtoupper($array[0]);

           if (!in_array($command, $commands)) {
              return false;
           }

           if ($arr_len <= 2) {
               return false;
           }
           else {
               if (strtoupper($array[1]) != 'TABLE') { //CREATE TABLE, ALTER TABLE
                   return false;
               }
           }

           $previous_value = strtoupper($array[$previous_index]);
           $next_value = strtoupper($array[$next_index]);

           if ( ($next_value == 'PRIMARY') && ($next_value+1 < $arr_len) ) {
              if (strtoupper($array[$next_value+1]) == 'KEY') {
                  return true;
              }
           }

           $conditions[] = in_array($next_value, $context_keywords); //INT AUTO_INCREMENT
           $conditions[] = $previous_value != ','; //Cannot be: ,INT
           $conditions[] = ($next_value == '(') || ($next_value == ','); //i.e. "VARCHAR(", or "id INT, "

           return ($conditions[0]) || ($conditions[1]) || ($conditions[2]);
       }

       return false;
    }

    function is_table($keyword, &$index, &$array){
        
        //If still unused in the future, remove it
        //$keyword = strtoupper($keyword);

        $arr_len = count($array);

        $context_keywords = array('TABLE', 'INTO', 'JOIN', 'FROM', 'ON', 'UPDATE');

        $previous_index = $index-1;
        $next_index = $index +1;

        //Matches: "create TABLE myTable", "insert INTO myTable, "inner JOIN myTable", "FROM myTable",
        //"inner join another_table ON myTable"
        if ($previous_index >= 0){

            $previous_value = strtoupper($array[$previous_index]);
            
            if (in_array($previous_value, $context_keywords)) {
                return true;
            } 

            $previous_value_was_a_table = strpos($previous_value,'`') === 0;

            //tables can't be followed directly by other tables without a comma.
            //Exceptions to the rule will be handled below
            if ($previous_value_was_a_table) {
                
                return false;
            } //end
        }

        /**
         * Ensuing code make sure 'countries' at the end of following
         * string:
         * 
         *      select people.firstname, countries.name as country from people, countries
         * 
         * is identified as a table name
         */
        if ( is_context('SELECT', $index, $array) ) {
            if (found_keyword_walking_backward('FROM', $index, $array, '', true, array('`'), true)) {
               return true;
            }            
        }

        //Matches table alias, i.e. "inner join another_table ON myTable.myField = 
        //another_table.another_field"
        if ($next_index < $arr_len) {
           $next_value = $array[$next_index];
           if ($next_value == '.') {
               return true;
           }
        }

       return false;
    }

    function is_field($keyword, &$index, &$array){
        
        //If still unused in the future, remove it
        $keyword = strtoupper($keyword);

        $arr_len = count($array);

        $context_tokens = array();

        /**
         * Depending on the context, keywords have different meanings.
         * 
         * I.e. if the context (that is: the type of SQL statement) is:
         * 
         *      CREATE TABLE
         * 
         * the keyword AFTER is always followed by a column name.
         * 
         * But, if the context is: 
         * 
         *      CREATE TRIGGER
         * 
         * the keyword AFTER can be followed by UPDATE, INSERT, etc.
         */
        $context_tokens['ALTER TABLE']['BEFORE'] = array('AFTER', 'CHANGE');

        //Dot matches table alias, i.e. "inner join another_table on myTable . myField = 
        //another_table . another_field"
        $context_tokens_before = 
            array('SELECT', 'WHERE', 'AS', '.', 'TO', 'COLUMN', 'AFTER');

        $context_tokens_after = 
            array('!=', '<=', '>=', '=', '<>', '<', '>', 'AS', 'LIKE', 'BETWEEN', 'IS', 'NOT', 'FROM', 'IN');

        $datatypes = array('CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'BLOB', 'MEDIUMTEXT', 'MEDIUMBLOB', 'LONGTEXT', 
                           'LONGBLOB', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT', 'FLOAT', 'DOUBLE', 
                           'DECIMAL', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'ENUM', 'SET', 'BOOLEAN');

        $previous_index = $index-1;
        $next_index = $index +1;

        $previous_value = '';
        $next_value = '';

        //Matches: "create TABLE myTable", "insert INTO myTable, "inner JOIN myTable", "FROM myTable",
        //"inner join another_table ON myTable"
        if ($previous_index >= 0){

            $previous_value = strtoupper($array[$previous_index]);
            
            
            if (in_array($previous_value, $context_tokens_before)) {

                    
                    switch ($previous_value) {

                        case '.':
                        case 'AS':
                        case 'TO':
                        case 'COLUMN':
                            return true;

                        default:
                            break;
                    }
                    
                    //Prevents erroneusly identifying DISTINCT as field (i.e. SELECT DISTINCT surname 
                    //FROM names)
                      if ($next_index < $arr_len) {
                         $next_value = strtoupper($array[$next_index]);

                        //Catch everything following next BUT a whitespce. A 
                        //whitespace tells for sure it's not a field
                        if (in_array($next_value, $context_tokens_after)) {
                            return true;
                        } else if ($next_value == ','){
                           return true;
                        }
                        else {
                            return false;
                        }
                    }else {
                        return false;
                    }

            } 

            /**
             * Tells us the statement type, that is: how the statement starts.
             * 
             * I.e. ALTER TABLE, CREATE TRIGGER, etc.
             */
            $contexts = array_keys($context_tokens);
            

            foreach ($contexts as $value) {

                /**
                 * If the previous value is:
                 * 
                 *      AFTER
                 * 
                 * and context is:
                 * 
                 *      CREATE TABLE
                 * 
                 * checks whether this AFTER keyword is inside a CREATE TABLE statement
                 */
                if (is_context($value, $previous_value, $array)) {

                    /**
                     * Once verified the context, checks whether this keyword is supported
                     * in this context.
                     * 
                     * For example, if the context is ALTER TABLE, then any keyword following
                     * the keyword AFTER is a field.
                     */
                    if (in_array($previous_value, $context_tokens[$value]['BEFORE'])) {
                       return true;
                    }
                }
            }

            $previous_value_was_a_table = strpos($previous_value,'`') === 0;

            //Fields can't be followed directly by other fields without a comma.
            //Exceptions to the rule are handled below
            if ($previous_value_was_a_table) {
                
                /**
                 * Handling the likes of:
                 * 
                 *      ALTER TABLE `MyTable` CHANGE COLUMN foo bar VARCHAR(32) NOT NULL FIRST
                 * 
                 * or:
                 * 
                 *      ALTER TABLE `MyTable` CHANGE COLUMN foo bar VARCHAR(32) NOT NULL AFTER baz
                 * 
                 * assuming current value is "foo" or "bar"
                 */
                if (is_context('ALTER TABLE', $index, $array)) {

                    if ($previous_index-1 > 0) {

                        /*
                         *
                         * Handles the likes of: 
                         * 
                         *      CHANGE `column1` `column2` VARCHAR(25)
                         * 
                         * Assuming current value is "column2"
                         */
                        $CHANGE_keyword = strtoupper($array[$previous_index-1]);
    
                        if ($CHANGE_keyword != 'CHANGE') {
    
                            if ($previous_index-2 > 0) {
    
                                /*
                                * Could be:
                                * 
                                *  CHANGE COLUMN `column1` `column2` VARCHAR(25)
                                * 
                                * Assuming current value is "column2"
                                */
                                $CHANGE_keyword = strtoupper($array[$previous_index-2]);
    
                                if ($CHANGE_keyword == 'CHANGE') {
                                    return true;
                                }
                            }
                            
                        }
                        else {
                            return true;
                        }
    
                    }
                }
                
                return false;
            }
        }

        if ($next_index < $arr_len) {
            $next_value = strtoupper($array[$next_index]);
            
            switch ($previous_value) {

                case 'DISTINCT':
                    if ( ($next_value == ',') || ($next_value == 'FROM') ) {
                        return true;
                    }

                default:
                    break;
            }

            /**
             * Catches "=" in: WHERE field = value.
             */
            if (in_array($next_value, $context_tokens_after)) {
                return true;
            }
        
            //If the next field contains a datatype, it is a table definition.
            //I.e. create table people (id INT 
            if (in_array($next_value, $datatypes)) {
                return true;
            }
         }

         /**
         * If preeceded by a opening parenthesis or a comma could be a field, unless it is 
         * followed immediately after by another opening parenthesis, indicating it's a 
         * function (i.e. "SELECT (SUM( (SUM(1+2) + 4 ) )". Make sure of that:
         */
         if ( (!empty($previous_value)) && (!empty($next_value)) ) {
             if ( ($previous_value == ',') || ($previous_value == '(') ) {
                 if ( ($next_value == ',') || ($next_value == ')') ) {
                     return true;
                 }
             }
         }

         //$items_to_skip = array('`', ',');

        /**
         * Go back, OPTIONALLY go through previous items recursively ( strpos('`' ...)  
         * and strpos(',' ...) ), until you find BY. When found, look at the item before 
         * it. If it's GROUP, this is a field.
         */
        if (found_GROUP_BY_walking_backward($index, $array)) {
            return true;
        }

        //The same as above for 'ORDER BY
        if (found_ORDER_BY_walking_backward($index, $array)) {
            return true;
        }

       return false;
    }

    function is_database($keyword, &$index, &$array){
        $keyword = strtoupper($keyword);

        $previous_index = $index-1;
        $next_index = $index +1;

        $previous_value = '';
        $next_value = '';

        if ($previous_index >= 0){

            $previous_value = strtoupper($array[$previous_index]);

            switch ($previous_value) {

                case 'DATABASE':
                    return true;
                    break;

                case 'USE':

                    /**
                     * Get value at the start of the string or after the first 
                     * semicolon, if there are multiple statements.
                     */
                    if (is_context('USE', $index, $array)) {
                        return true;
                        break;
                    }

                default:
                    # code...
                    break;
            }
        }

        return false;
    }

    function is_not_reserved_keyword(&$keyword, &$keywords_multi_dim){

        $keyword = strtoupper($keyword);

        /**
         * Requires a multi-dimensional array containing keyword 
         * categories.
         * 
         * Example:
         * 
         *      $keywords_multi_dim['top_level'] could contain: 'SELECT', 'INSERT', 'DELETE', 'UPDATE', ...
         *      $keywords_multi_dim['functions'] could contain: 'SUM', 'AVG', 'MIN', 'MAX',...
         *      etc.
         */
        foreach ($$keywords_multi_dim as $key => $value) {
            if (in_array($keyword, $value)) {
               return false;
            }
        }

        return true;
    }

    function is_keyword_followed_by_keyword($keyword, &$index, &$array){
        
        $keyword = strtoupper($keyword);

        $keywords = array();

        $keywords['ALTER'] = array('TABLE');
        $keywords['CREATE'] = array('DATABASE', 'INDEX', 'TABLE');
        $keywords['CROSS'] = array('JOIN');
        $keywords['DELETE'] = array('FROM');
        $keywords['DROP'] = array('DATABASE', 'INDEX', 'TABLE');
        $keywords['GROUP'] = array('BY');
        $keywords['FOREIGN'] = array('KEY');
        $keywords['INNER'] = array('JOIN');
        $keywords['INSERT'] = array('INTO');
        $keywords['LEFT'] = array('JOIN', 'OUTER');
        $keywords['NOT'] = array('NULL');
        $keywords['ORDER'] = array('BY');
        $keywords['OUTER'] = array('JOIN');
        $keywords['PRIMARY'] = array('KEY');
        $keywords['RIGHT'] = array('JOIN', 'OUTER');
        $keywords['SELECT'] = array('FROM');
        $keywords['VALUES'] = array('(');

        $arr_len = count($array);

        if ($keyword == 'SET') {
            $two_index_forward = $index + 2; //Which is "=" in "SET field = ...";

            if ($two_index_forward < $arr_len) {
                if ($array[$two_index_forward] == '=') {
                   return true;
                }
            }

        }

        $keys = array_keys($keywords);

        if (in_array($keyword, $keys)) {

            $next_index = $index +1;

            if ($next_index < $arr_len) {
                
                $next_value = strtoupper($array[$next_index]);

                if (in_array($next_value, $keywords[$keyword])) 
                    return true;
            }

        }

        return false;
    }

    function is_keyword_preceeded_by_keyword($keyword, &$index, &$array){

        $keyword = strtoupper($keyword);

        $keywords = array();

        $keywords['BY'] = array('GROUP', 'ORDER');
        $keywords['DATABASE'] = array('CREATE', 'DROP');
        $keywords['FROM'] = array('DELETE', 'SELECT');
        $keywords['INDEX'] = array('CREATE', 'DROP');
        $keywords['INTO'] = array('INSERT');
        $keywords['JOIN'] = array('CROSS', 'INNER', 'LEFT', 'OUTER', 'RIGHT');
        $keywords['KEY'] = array('PRIMARY', 'FOREIGN');
        $keywords['NULL'] = array('NOT');
        $keywords['OUTER'] = array('LEFT', 'RIGHT');
        $keywords['TABLE'] = array('ALTER', 'CREATE', 'DROP');  

        $keys = array_keys($keywords);

        if (in_array($keyword, $keys)) {

            $previous_index = $index -1;

            if ($previous_index >= 0) {
                
                $previous_value = strtoupper($array[$previous_index]);

                if (in_array($previous_value, $keywords[$keyword])) return true;
            }

        }

        return false;

    }

    function is_numeric_value(&$keyword){
        return is_numeric($keyword);
    }

    function is_string_value(&$keyword){

        $skip_condition1 = (strpos($keyword, '\'') == 0) && (strpos($keyword, '\'') !== false);
        $skip_condition2 = (strpos($keyword, '"') == 0) && (strpos($keyword, '"') !== false);

        return $skip_condition1 || $skip_condition2; 
    }

    function is_dbnull(&$keyword){

        return (strtoupper($keyword) === 'NULL');
    }

    function is_boolean(&$keyword){
        return ( (strtoupper($keyword) === 'FALSE') || (strtoupper($keyword) === 'TRUE') );
    }

    function query_add_backticks(&$query_arr){
        
        //"$i= 1" skips initial keyword (that is: SELECT, INSERT...)
        for ($i=1; $i < count($query_arr); $i++) { 

            $query_part = $query_arr[$i];        
            
            $keyword = strtoupper($query_part);
                
            switch (true) {

                //Try sorting use cases from the most common to the least common one

                case is_dbnull($keyword):
                case is_boolean($keyword):
                case is_keyword_followed_by_keyword($keyword, $i, $query_arr):
                case is_keyword_preceeded_by_keyword($keyword, $i, $query_arr):
                case is_string_value($keyword):
                case is_numeric_value($keyword):
                case is_punctuation($keyword):
                case is_empty($keyword):
                case is_datatype($keyword, $i, $query_arr):
                    break;

                case is_comma($keyword):
                    break;

                case is_field($keyword, $i, $query_arr):
                case is_table($keyword, $i, $query_arr):
                case is_database($keyword, $i, $query_arr):
                    
                    //Do not add backticks if they already exists.
                    $first_backtick_found = (strpos($query_part, '`') == 0) && (strpos($query_part, '`') !== false);
                    $last_backtick_found = strpos($query_part, '`', 1) == strlen($query_part)-1;

                    $query_arr[$i] = $first_backtick_found && !$last_backtick_found ? "$query_part`"
                                     : $last_backtick_found && !$first_backtick_found ? "`$query_part"
                                     : $last_backtick_found && $last_backtick_found ? $query_part
                                     : "`$query_part`";
                    break;

                default:
                    break;
            }
        }

        return $query_arr;
    }

    function query_apply_formatting($query_arr, $beautify){

        $start_index = '';
        $end_index = '';
        $formatted_array = array();
        $arr_len = count($query_arr);
        $formatted = '';

        for ($i=0; $i < $arr_len; $i++) { 
            
            $value = $query_arr[$i];

            $beginning_single_quote_found = (strpos($value, '\'') == 0) && (strpos($value, '\'') !== false);
            $beginning_double_quote_found = (strpos($value, '"') == 0) && (strpos($value, '"') !== false);

            if ( !$beginning_single_quote_found && !$beginning_double_quote_found ) {

                if ($start_index === '') {
                     $start_index = $i;

                }  
            }
            else {
                if ($i > 0) {
                    $end_index = $i-1;
                }
            }

            /**
             * If the statement passed as argument contains only operators,
             * clauses and expressions (so: no string values), $end_index
             * would never be set. 
             * 
             * So, check if at the end of the iteration we have a $start_index
             * set without a matching $end_index and, in case, assign to the latter
             * the end of the array.
             */
            if (($start_index !== '') && $i == $arr_len-1) {
                $end_index = $i;
            }

            if ( ($start_index !== '') && ($end_index !== '') ) {
                
                $len = ($end_index - $start_index) + 1;

                $slice = array_slice($query_arr, $start_index, $len);

                $slice = implode(' ', $slice);

                if ($beautify) {
            
                    $slice = sql_beautify($slice);
                    $slice = sql_format($slice, false);
                    
                }
                else {
                    $slice = sql_format($slice);
                }

                $formatted_array[] = $slice;

                if ($i < $arr_len-1) {

                   //Add the string value (that is: a value starting with "'" or '"') as is
                    $formatted_array[] = $value;
                }

                //Reset
                $start_index = '';
                $end_index = '';
            }

        }

        if (empty($formatted_array)) {

           $formatted = implode(' ', $query_arr);
           $formatted = sql_format($formatted);

           if ($beautify) {
                $formatted = sql_beautify($formatted);
            }
        
        }
        else {

            $formatted = implode(' ', $formatted_array);
            $formatted = sql_format($formatted, false);

        }

        return $formatted;
    }

    function sql_format($string_query, $remove_redundant_spaces = true){
        
        if ($remove_redundant_spaces) {

            //Remove redundant whithespaces (leave only single ones)
            $string_query = preg_replace('/[\s\p{Zs}]{2,}/m', ' ', $string_query);
        }
        
        //No spaces after dots (i.e. we want: `table`.`field` not: `table`. `field`)
        $string_query = str_replace('. ', '.', $string_query);

        //No spaces before dots (i.e. we want: `table`.`field` not: `table` .`field`)
        $string_query = str_replace(' .', '.', $string_query);

        //No spaces after opening parenthesis
        if (strpos($string_query, '( ') !== false) {

            $string_query = str_replace('( ', '(', $string_query);
        }

        //No spaces before closing parenthesis
        if (strpos($string_query, ' )') !== false) {
            
            $string_query = str_replace(' )', ')', $string_query);
        }

        //No spaces before semicolon
        if (strpos($string_query, ' ;') !== false) {

            $string_query = str_replace(' ;', ';', $string_query);
        }

        //No spaces before comma
        if (strpos($string_query, ' ,') !== false) {

            $string_query = str_replace(' ,', ',', $string_query);
        }

       return $string_query;
    }

    //Depends on global $keywords
    function sql_beautify($query, $is_assigned_string_value_in_query = false) {

        global $keywords;

        $newline = "\n";
        $space = space(6);
        $replace_keyword = '';
        $conflict_prevention_token = '@@_@*';

        //Add a newline after each field
        if (preg_match('/(, )/im', $query)) {
            $query = preg_replace('/(, )/im', ",$newline$space", $query);
        }

        foreach ($keywords['reserved_no_newlines'] as $keyword) {
            
            /* (Keyword search) 
            *      
            * assuming: $keyword = 'DESC', it matches:
            * 
            *      "... DESC ..." (inside the string)
            *      "... DESC" (end of string)
            *
            * it matches also:
            * 
            *      "... DESC;" (inside the/end of string)
            *      "... DESC," (inside the/end of string) 
            * 
            * These kind of keywords are not allowed at the beginning of a
            * SQL statement.
            * 
            * "/^()($keyword)(\s{1,1})|([\s\(]{1,1})($keyword)([;{1,1},{1,1}\s{1,1}])|(\s{1,1})($keyword)$/im"
            */
            if (preg_match("/^()($keyword)(\s{1,1})|([\s\(]{1,1})($keyword)([;{1,1},{1,1}\s{1,1}])|(\s{1,1})($keyword)" . '$/im', 
                           $query)) {

                //Do not add new spaces or newlines, simply uppercase all matches (note: $keyword
                //is already uppercase)
                $query = preg_replace("/^()($keyword)(\s{1,1})|([\s\(]{1,1})($keyword)([;{1,1},{1,1}\s{1,1}])|(\s{1,1})($keyword)" . '$/im', 
                                      '$1$4$7' . avoid_keyword_conflict($keyword, $conflict_prevention_token) . '$3$6', 
                                      $query);
            }
            
        }

        foreach ($keywords['newline_before'] as $keyword) {

            /* (Keyword search)
                *      
                * assuming: $keyword = 'SELECT', it matches:
                * 
                *      "SELECT ..." (beginning the string)
                *      "... SELECT ..." (inside the string)
                *      "...SELECT" (end of string)
                *
                * Don't worry about the newline at the beginning of the keyword. It will
                * be stripped out before returning the string.
                * 
                * "/^()($keyword)(\s{1,1})|([\s\(]{1,1})($keyword)(\s{1,1})/im"
                */
            if (preg_match("/^()($keyword)(\s{1,1})|([\s\(]{1,1})($keyword)(\s{1,1})/im", 
                           $query)) {
                
                //These keywords simply start a newline and keep 
                //following text on the same line
                $query = preg_replace("/^()($keyword)(\s{1,1})|([\s\(]{1,1})($keyword)(\s{1,1})/im", 
                                      "$1$4$newline" . avoid_keyword_conflict($keyword, 
                                      $conflict_prevention_token) . '$6$3', 
                                      $query);
            }
            
        }

        foreach ($keywords['newline_before_and_after'] as $keyword) {

            $pattern = "/(\s{0,1})($keyword)(\s{1,1})/im";

            if (preg_match($pattern, 
                           $query)) {

                
                //These keywords stay alone on their own line 
                $query = preg_replace($pattern, 
                                      "$1$newline" . avoid_keyword_conflict($keyword, 
                                      $conflict_prevention_token) . "$3$newline$space", 
                                      $query);
            }
        }

        foreach ($keywords['functions_no_newlines'] as $keyword) {

            /* (Function name search)
            *      
            * assuming: $keyword = 'LOCATE', it matches:
            * 
            *  "LOCATE (..." (beginning of a string)
            *  "LOCATE(..." (beginning of a string)
            * 
            *  "... LOCATE ( ..." (inside the string)
            *  "... LOCATE( ..." (inside the string)
            *
            * "/^()($keyword)(\s{0,1}\({1,1})|([\s\(]{1,1})($keyword)(\s{0,1}\({1,1})/im"
            */
            if (preg_match("/^()($keyword)(\s{0,1}\({1,1})|([\s\(]{1,1})($keyword)(\s{0,1}\({1,1})/im", 
                           $query)) {

         
                //Do not add new spaces or newlines before/after a function name; simply 
                //uppercase all matches (note: $keyword is already uppercase) 
                $query = preg_replace("/^()($keyword)(\s{0,1}\({1,1})|([\s\(]{1,1})($keyword)(\s{0,1}\({1,1})/im", 
                                      '$1$4' . avoid_keyword_conflict($keyword, $conflict_prevention_token)  . '$3$6', 
                                      $query);
            }
        }

        foreach ($keywords['datatypes_no_newlines'] as $keyword) {

            /* (Keyword search) 
            *      
            * assuming: $keyword = 'VARCHAR', it matches:
            * 
            *      "... VARCHAR ..." (inside the string)
            *      "... VARCHAR ( ..." (inside the string)
            *      "... VARCHAR( ..." (inside the string)
            *
            * "/(\s{1,1})($keyword)(\s{0,1}\({0,1})/im"
            */
            if (preg_match("/(\s{1,1})($keyword)(\s{0,1}\({0,1})/im", 
                           $query)) {

                //Do not add new spaces or newlines, simply uppercase all matches (note: $keyword
                //is already uppercase)
                $query = preg_replace("/(\s{1,1})($keyword)(\s{0,1}\({0,1})/im", 
                                      '$1' . avoid_keyword_conflict($keyword, $conflict_prevention_token) . '$3', 
                                      $query);
            }
            
        }
      
        $query = str_replace($conflict_prevention_token, ' ', $query);

        //No spaces before opening parenthesis
        if (strpos($query, ' (') !== false) {
            
            $query = str_replace(' (', '(', $query);
        }

        return $query;
    }

    /**
     * Example:
     * 
     *      SELECT FROM users WHERE `firstname` = 'Mary Jane Watson';
     * 
     * The string value (assigned on the right of the equal sign) is:
     * 
     *      'Mary Jane Watson'
     */
    function is_assigned_string_value_in_query($value){

        /**
         * Reasons for "trim":
         * 
         * replace:
         * 
         *      "'insert date into table', "
         * 
         * with:
         * 
         *       "'insert date into table'," (ending space removed)
         * 
         * or replace:
         * 
         *      "'insert date into table' "
         * 
         * with:
         * 
         *       "'insert date into table'" (ending space removed)
         */
        return preg_match('/^(\'{1,1}[\w\d\s\p{P}\n]*\'{1,1}),{0,1}$/mu', trim($value));
    }

    function reset_exception_result(){
        global $exception_result;
        $exception_result = array('errno' => 0, 'error' => '', 'success' => true, 'error type' => '');
    }

    function handle_exceptions(){
        
                /**
                 * This variable is populated with the result of the call to
                 * mysqli_query_validate()
                 */
                global $exception_result;
                global $msg_tag;

                if (!$exception_result['success']) {
                   
                   /**
                    * All errors in "case" are ignored. 
                    * 
                    * Fatal error are processed in the "default" clause.
                    */
                    switch ($exception_result['errno']) {

                        /**
                         * Error -2 is:
                         * 
                         *  Could not validate query. EXPLAIN only supports SELECT, 
                         *  DELETE, INSERT, REPLACE, and UPDATE
                         */
                        case '-2':
                        $msg_tag = '<span class="msg" style="color:orange;font-size:16px">' 
                                        . $exception_result['error'] .
                                   '</span>';

                        /**
                         * Error 1054 is:
                         * 
                         *  Unknown column '<column name>' in 'field list'
                         * 
                         * yes, we know column does not exist, we're testing
                         * syntax is correct here, so move on if this error
                         * is encountered.
                         */
                        case '1054':

                        /**
                         * Error 1146 is:
                         * 
                         *  Table '<database_name>.<table_name>' doesn't exist
                         * 
                         * yes, we know table does not exist, we're testing
                         * syntax is correct here, so move on if this error
                         * is encountered.
                         */
                        case '1146':
                            $exception_result['success'] = true; //Continue processing. Just issue a warning and move on.
                            break;

                        default:

                        if ($exception_result['errno'] == '1064') {

                            //Shorten error message
                            $exception_result['error'] = 'You have an error in your SQL syntax.';
                        }
                        
                        $msg_tag = '<span class="msg" style="color:red;font-size:16px">' 
                                        . $exception_result['error'] .
                                   '</span>';  
                                                             
                            break;
                    }
                }
    }

    function found_keyword_walking_backward($str_keyword_to_find, 
                                            $current_index, 
                                            $arr_query,
                                            $str_keyword_before_keyword_to_find = '',

                                            /**
                                             * If TRUE, checks whether the current item STARTS whith
                                             * any of values stored in $arr_items_to_skip.
                                             * 
                                             * If FALSE, check whether the current item EQUALS
                                             * any of values stored in $arr_items_to_skip.
                                             */
                                            $bool_match_at_start = false,
                                            $arr_items_to_skip = array(),

                                            /**
                                             * Should commas be ignored when walking backward?
                                             */
                                            $skip_comma = false){

        $previous_index = $current_index;

        while (--$previous_index > 0) {

                                                    //Decremented by 1 at the start of the loop
            $previous_value = strtoupper($arr_query[$previous_index]);

            if ($skip_comma) {
                

                    /**
                     * ***************** DELETE ************************************
                     * For formatting reasons, commas are moved at the end of the value
                     * preeceding the comma.
                     * 
                     * I.e.
                     * 
                     *      array(
                     *          [...] => ...
                     * 
                     *          [10] => '`table_name`',
                     *          [11] => ',',
                     * 
                     *          [...] => ...
                     *      )
                     * 
                     * Becomes:
                     * 
                     *      array(
                     *          [...] => ...
                     * 
                     *          [10] => '`table_name`, ',    <= SEE THE COMMA HAS BEEN MOVED HERE...
                     *          [11] => null,                <= ...AND HERE HAS BEEN SET TO NULL
                     * 
                     *          [...] => ... 
                     *      )
                     */
                    // switch ($previous_value) {
                    //     case null:
                    //         $two_values_back = $arr_query[$previous_index-1];
                    //         if (strrpos($two_values_back, ', ') == (strlen($two_values_back)-2) ) {
                    //             continue;
                    //         }
                        
                    //     case ',':
                    //        continue;
                        
                    //     default:
                    //         # code...
                    //         break;
                    // }
                    // ***************** END DELETE ************************************
                    if (is_comma($previous_value)) {
                        continue;
                    }
                    
                }

            $item_to_find = empty($str_keyword_before_keyword_to_find) ? 
                                   strtoupper($str_keyword_to_find) : 
                                   strtoupper($str_keyword_before_keyword_to_find);

            if ($previous_value == $item_to_find) {
                    if (!empty($str_keyword_before_keyword_to_find)) {
                       if (($previous_index-1) > 0) {

                            $item_to_find =  strtoupper($str_keyword_to_find);
                            $previous_value = strtoupper($arr_query[$previous_index-1]);

                           if ($previous_value == $item_to_find) {
                               return true;
                           }
                           else {
                               return false;
                           }
                       }
                       else {
                           return false;
                       }
                    }
                    else {
                        return true;
                    }
            }

            /**
             * Go backward until $str_keyword_before_keyword_to_find
             * is found or $str_keyword_to_find is found
             */
            foreach ($arr_items_to_skip as $value) {

                $condition =  $previous_value ==  strtoupper($value);

                if ($bool_match_at_start) {
                    $condition = strpos($previous_value, $value) === 0;
                }

                if ($condition) {
                    continue;
                }
                else {
                    if ($previous_value != $str_keyword_to_find) {
                        if (!empty($str_keyword_before_keyword_to_find)) {
                            if ($previous_value != $str_keyword_before_keyword_to_find) {
                                return false;
                            }
                        }
                        else {
                            return false;
                        }
                    }
                    break;
                }
            }
        }

        return false;
    }

    function found_GROUP_BY_walking_backward($current_index, 
                                             $arr_query,
                                             $bool_match_at_start = false,
                                             $arr_items_to_skip = array()){

    
        return found_keyword_walking_backward('GROUP', 
                                              $current_index, 
                                              $arr_query,
                                              'BY',
                                              $bool_match_at_start,
                                              $arr_items_to_skip);

    }

    function found_ORDER_BY_walking_backward($current_index, 
                                             $arr_query,
                                             $bool_match_at_start = false,
                                             $arr_items_to_skip = array()){

    
        return found_keyword_walking_backward('ORDER', 
                                              $current_index, 
                                              $arr_query,
                                              'BY',
                                              $bool_match_at_start,
                                              $arr_items_to_skip);

    }

    /*
    * Check wheter current item is inside the specified SQL statement.
    * 
    * I.e. assuming the following statement:
    * 
    *      ALTER TABLE MyTable CHANGE COLUMN foo bar VARCHAR(32) NOT NULL AFTER baz;
    * 
    *  assuming item is set to: 
    * 
    *      AFTER 
    * 
    * check wheter the SQL statement starts with: 
    * 
    *      ALTER TABLE
    */
    function is_context($context_keywords, $item_index, $array, $value_to_check=';'){


        $context_keywords = trim($context_keywords);
        
        $context_keywords = explode(' ', $context_keywords); //I.e. ALTER TABLE

        $occurrences = array();

        $args = array();

        $args[0] = $value_to_check;
        $args[1] = &$occurrences;

        array_walk($array, 'detect_duplicates', $args);

        sort($occurrences);

        $context_start_at = -1;

        foreach ($occurrences as $occurrence_index) {

            /*
            * Occurrences are sorted.
            * 
            * Keep into account only occurrences PRECEEDING
            * current item.
            */
            if ($occurrence_index > $item_index) {
                break;
            }

            //Record the closest occurrence
            if ($occurrence_index < $item_index) {
                $context_start_at = $occurrence_index;
            }

        }

        $context_start_at++;

        if ( ($context_start_at + count($context_keywords)) > count($array) ) {
            return false;
        }

        $result = true;

        //If $context_keywords is set to "0 => ALTER, 1 => TABLE", verifies
        //that, starting at $context_start_at, value at the first index is:
        //ALTER and value at the second index is: TABLE
        foreach ($context_keywords as $keyword) {
            $result = $result && (strtoupper($array[$context_start_at]) == strtoupper($keyword));
            $context_start_at++;
        }

        return $result;
        
    }

    //Works when used as callback in array_walk()
    function detect_duplicates($value, $key, &$args){
            
        $value_to_check = $args[0];
        $occurrences = &$args[1];

        if ($value == $value_to_check) {
            $occurrences[] = $key;
        }
    }

    function array_filter_empty_only($input_array){

        /**
         * array_filter() preserves keys (indexes).
         * 
         * We don't want that.
         * 
         * We want remove empty values, and return a new array starting at zero.
         */
        $return_array = array_values(array_filter($input_array, 'query_trim', ARRAY_FILTER_USE_BOTH)) ;
        return $return_array;
    }

    /*     
        MODIFIES THE ORIGINAL STRING.

        It doesn't return a new string.

        Performs two operations:
            
        1.  Add spaces before and after the following comparison operators:
        
                '!=', '<=', '>=', '=', '<>', '<', '>'
        
        2.  Removes redundant whitespaces. 
    */
    function query_normalize(&$query){
        
        $subject = &$query;
        
        $punctuations = array('(',')', ',', '+', '-', '*', '/', '%', '!=', '<=', '>=', '=', '<>', '<', '>', '.', ';');

        foreach ($punctuations as $punctuation) {
            
            if (strpos($subject, $punctuation)!==false) {
                $subject = str_replace($punctuation, " $punctuation ", $subject);
            }
        }

        //Finally, remove redundant whithespaces (leave only single ones)
        $subject = preg_replace('/[\s\p{Zs}]{2,}/m', ' ', $subject);
    }

    /* HELPER FUNCTIONS. NOT USED IN CODE */

    function utility__sort_and_return_array_as_code_with_values_on_single_line($array_arr, $string_arr_name, $html = false){
        
        $newline = "\n";
        $tab = "\t";

        if ($html) {
            $newline = '<br/>';
            $tab = '&nbsp;&nbsp;&nbsp;';
        }

        //remove duplicates
        $array_arr = array_unique($array_arr);

        sort($array_arr);

        $string_code = '$'. $string_arr_name . ' = array(';
        
        for ($i=0; $i < count($array_arr); $i++) { 

            $value = $array_arr[$i];

            if ($i!=count($array_arr)-1) {
                $string_code .= "'$value', $newline";
            }
            else {
                $string_code .= "'$value'";
            }
        }

        $string_code .= ');';

        return $string_code;
    }

    function utility__sort_and_return_array_as_code($array_arr, $string_arr_name){
            
        sort($array_arr);

        $string_code = '$'. $string_arr_name . ' = array(';
        
        for ($i=0; $i < count($array_arr); $i++) { 

            $value = $array_arr[$i];

            if ($i!=count($array_arr)-1) {
                $string_code .= "'$value', ";
            }
            else {
                $string_code .= "'$value'";
            }
        }

        $string_code .= ');';

        return $string_code;
    }

    function utility__create_and_return_assoc_array_as_code_arrows($array_arr_as_code, $arr_arr_keys, $html = false){
        $newline = "\n";
        $tab = "\t";

        if ($html) {
            $newline = '<br/>';
            $tab = '&nbsp;&nbsp;&nbsp;';
        }

        $string_result = '$myAssocArray = array(' . "$newline$tab";

        for ($i=0; $i < count($array_arr_as_code); $i++) { 

            $string_result .= "$arr_arr_keys[$i] => array($newline$tab";

            $string_curr_arr = $array_arr_as_code[$i];

            $start_index = strpos($string_curr_arr, '(');

            $string_curr_arr = substr($string_curr_arr, $start_index+1,  strlen($string_curr_arr) - (($start_index +1) + 2) );

            $string_result .= $string_curr_arr;

            $string_result .= "$newline),$newline";
        }

        $string_result = substr($string_result, 0, strlen($string_result));

        $string_result .= ');';

        return $string_result;
    }

    function utility__sort_two_dim_arr_and_output_as_code($two_dim_array, $array_name, $html = false){
        
        $newline = "\n";
        $tab = "\t";

        if ($html) {
            $newline = '<br/>';
            $tab = '&nbsp;&nbsp;&nbsp;';
        }

        $string_result = '';

        ksort($two_dim_array, SORT_STRING);

        foreach ($two_dim_array as $key => $value) {
            $string_result .= '$' . $array_name . '[\'' . $key . '\'] = array(';

            sort($value);

            for ($i=0; $i < count($value); $i++) { 
                if ($i!=count($value)-1) {
                    $string_result .= '\'' . $value[$i] .'\', ';
                }
                else {
                    $string_result .= '\'' . $value[$i] .'\'';
                }
            }

            $string_result .= ");$newline";
        }

        return $string_result;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MySql Query Formatter (add backticks around field and table names)</title>
    <link rel="icon" href="favicon.ico">
    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
        }

        #query_div, #result_div{
            width: 48%;
            position: relative;
            display: inline-block;
        }

        #query_div{
            margin-right: 3%;
        }

        .textarea{
            width: 100%;
            height: 300px;
            font-family: Arial, Helvetica, sans-serif;
            color: <?php echo $input_textarea_style; ?>;
            resize: none;
            padding: 10px;
        }

        #result{
            color: blue;
        }

        textarea{
            resize: none;
            border: 2px solid black;
        }

        #btnSubmit{
            padding: 10px;
        }

        #author{
            position: relative;
            left: 250px;
            top: -25px;
        }

        .info{
            font-size: 0.85em;
        }

        .container {
                min-width: 995px; 
            }

        .msg{
            line-height: 40px;
        }
    </style>

    <link rel="stylesheet" href="bootstrap3/css/bootstrap.min.css">
    <link rel="stylesheet" href="balloon/balloon.css">

    <script>
        function inputTextarea_Focus(){
            
            document.getElementById('query').style.color = "black";
            document.getElementById('query').placeholder = "";
        }

        function inputTextarea_Blur(){
            
            if(document.getElementById('query').value == ""){

                document.getElementById('query').style.color = "rgb(128, 120, 120)";
                document.getElementById('query').placeholder = "Insert query...";

            }
            
        }
    </script>
</head>
<body>
    <div class="row">

        <div class="container col-xs-12" id="wrapper">

            <header>
                <div class="container mt-3">
                    <div id="slogan">
                        <h1>MySql Add Backticks</h1>
                    </div>

                    <div id="author" class="pt-2">
                        <h3>By Francesco Torre</h3>
                    </div>
                </div>
            </header>

            <main>
                <div class="container">
                <form action="mysqladdbackticks.php" method="post">
                            <div id="query_div">
                            <textarea name="query" 
                                onBlur="inputTextarea_Blur()"
                                onFocus="inputTextarea_Focus()" 
                                class="textarea" 
                                id="query" 
                                placeholder="Insert query..."><?php echo $query_before;?></textarea>       
                        </div>

                        <div id="result_div">
                            <textarea name="result" class="textarea" id="result" readonly><?php echo $query_after;?></textarea>       
                        </div>
                        
                        <div style="width:48%"><span class="info"><b>Note:</b> comment marks (<b>#</b>, <b>--</b>, <b>/* */</b>) are removed automatically</span>
                            <div style="float:right">
                            <a href="#"
                            style="text-align: justify;"
                            class=""
                            data-balloon-length="xlarge"
                            aria-label='In MySql, field/table names are usually enclosed in backticks to avoid conflicts with reserved words, but the backtick key is not available on some keyboard layouts, and hence this program, which takes a query in input and automatically returns the same query in output with backticks placed where needed.
                            
                                        Instructions:

                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. Type a SQL query in the left pane
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. Click the "Add Backticks" button
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. Use the result printed in the right pane '
                            data-balloon-break
                            data-balloon-pos="up">

                                <span style="font-size:18px" class="glyphicon glyphicon-info-sign"></span>
                            </a>
                            </div>
                        </div>
                        <br/>
                        <span><input type="checkbox" <?php echo $beautify_on ?> name="chkBeautify" id="chkBeautify"> <label for="chkBeautify">Beautify</label></span>
                        &nbsp;&nbsp;&nbsp;
                        <span><input type="checkbox" <?php echo $validate_on ?> name="chkValidate" id="chkValidate"> <label for="chkValidate">Validate</label></span>
                        <span>(<b>Requires a connection to MySql server</b>)</span>
                        <div style="height:40px"><?php echo $msg_tag; ?></div>
                        <div style="margin-top:8px">
                            <input id="btnSubmit" class="btn btn-primary" type="submit" value="Add Backticks"> 
                        </div>
                    </form> 
                    
                </div>
            </main>

            <footer></footer>

        </div>
    </div>
</body>
</html>