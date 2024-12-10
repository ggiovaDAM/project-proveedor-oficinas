<?php
    require_once __DIR__ . "/../../../config.php";

    /**
     * Creates a connection to the database specified in ```$xmlFile``` and returns it.
     * This function reads database configuration from an XML file and validates it against an XSD schema.
     * It establishes a PDO connection using the provided credentials and settings.
     * 
     * Will default to an error page (```src/pages/error/error_page.php```) if:
     * - If ```$xmlFile``` doesn't exist: The XML configuration file cannot be found in the specified path.
     * - If the XSD schema file for validation is missing.
     * - If ```$xmlFile``` doesn't have the proper XML format: The XML file is malformed or corrupted.
     * - If ```$xmlFile``` doesn't follow the schema's structure.
     * 
     * Use: ```require_once PDO_DIR . "/db.php";```
     * 
     * @param string $xmlFile Path to the XML configuration file
     * @return PDO Returns PDO connection object if successful, on failure it redirects to an error page
     */
    function connectToDatabase(string $xmlFile): PDO {
        $xsdFile = PDO_DIR . "/server_config_validation.xsd";

        // Check if the XML and XSD files exist
        if (@file_exists($xmlFile) === false) {
            fail(
                "Database Error",
                "The file <b>" . htmlspecialchars($xmlFile) . "</b> was not found, please make sure the file exists and the path is written correctly!"
            );
        }
        if (@file_exists($xsdFile) === false) {
            fail(
                "Database Error",
                "The file <b>" . htmlspecialchars($xsdFile) . "</b> was not found, please make sure the file exists and the path is written correctly!"
            );
        }

        // Load and validate XML
        $xml = @simplexml_load_file($xmlFile);

        // Check if the XML file is properly formatted
        if ($xml === false) {
            fail(
                "Database Error",
                "The file <b>$xmlFile</b> does not have the correct XML format, please make sure the file is properly formatted!"
            );
        }

        // Validate against schema
        $dom = new DOMDocument();
        $dom->loadXML($xml->asXML());

        // Check if the XML file is properly validated with the XSD
        if (@$dom->schemaValidate($xsdFile) === false) {
            fail(
                "Database Error",
                "The file <b>$xmlFile</b> is not properly validated with the <b>XSD</b>!"
            );
        }

        // Extract configuration
        $config = [
            'dbtype' => (string)$xml->dbtype,
            'dbname' => (string)$xml->dbname,
            'host' => (string)$xml->host,
            'port' => (string)$xml->port,
            'user' => (string)$xml->user,
            'password' => (string)$xml->password
        ];

        // Build DSN (Data Source Name) - Contains the information required to connect to the database
        $dsn = sprintf("%s:dbname=%s;host=%s%s",
            $config['dbtype'], $config['dbname'], $config['host'],
            $config['port'] ? ";port=" . $config['port'] : ""
        );

        try {
            // Create PDO instance with UTF-8 support and error mode set to exceptions
            $pdo = new PDO($dsn, $config['user'], $config['password'], [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            return $pdo;
        } catch (PDOException $e) {
            // Failure to connect to the database
            fail(
                "Database Connection Error", 
                "Failed to connect to the database.",
                "<b>Error Details:</b> " . htmlspecialchars($e->getMessage())
            );
        }
    }

    function fail(string $title, string ...$paragraphs): void {
        startSession();
        
        $result = "<h1>" . htmlspecialchars($title) . "</h1>";
        foreach ($paragraphs as $paragraph) {
            $result .= "<p>$paragraph</p>";
        }

        $_SESSION["ERROR"] = $result;
        header("Location: " . ERROR_URL);
        exit();
    }