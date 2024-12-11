<?php
    require_once __DIR__ . "/../../config.php";

    /**
     * Starts a session if one doesn't exist.
     * 
     * This function ensures that a session is started, even if it's not already running.
     * It's useful for maintaining session state across multiple requests.
     * 
     * @return void
     */
    function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Handles errors by displaying a formatted error page.
     * 
     * With the exception of the title, the paragraphs are displayed as HTML paragraphs
     * and stored in the session using $_SESSION["ERROR"]. It then performs an automatic
     * redirect to resources/php/error_page.php, where the error message is displayed.
     *
     * @param string $title Error title/heading that will be displayed
     * @param string ...$paragraphs Variable number of error message paragraphs to display
     * @return void
     * 
     * @see startSession() Called internally to ensure session is started
     */
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

    /**
     * Checks if a file exists and displays an error page if it doesn't.
     * 
     * @param string $file File path
     * @param string $title Error title/heading
     * @return void
     * 
     * @see fail() Used to display the error page
     */
    function checkFileExists(string $file, string $title = "File Error!"): void {
        if (@file_exists($file) === false) {
            fail(
                $title,
                "The file <b>" . htmlspecialchars($file) . "</b> was not found, please make sure the file exists and the path is written correctly!"
            );
        }
    }

    /**
     * Validates an XML file against an XSD file.
     * Will display an error page if:
     * - The XML file does not exist
     * - The XSD file does not exist
     * - The XML file is not properly formatted
     * - The XML file is not validated with the XSD file
     * 
     * Will always return a SimpleXMLElement object if the XML file is properly formatted and validated with the XSD file.
     * 
     * @param string $xmlFile File path to the XML file
     * @param string $xsdFile File path to the XSD file
     * @param string $title Error title
     * @return SimpleXMLElement Returns the SimpleXMLElement object
     * 
     * @see fail() Used to display the error page
     * @see checkFileExists() Used to check if the XML and XSD files exist
     */
    function validateXML(string $xmlFile, string $xsdFile, string $title): SimpleXMLElement {
        // Check if the XML and XSD files exist
        checkFileExists($xmlFile, $title);
        checkFileExists($xsdFile, $title);

        // Load and validate XML
        $xml = @simplexml_load_file($xmlFile);

        // Check if the XML file is properly formatted
        if ($xml === false) {
            fail(
                $title,
                "The file <b>$xmlFile</b> does not have the correct XML format, please make sure the file is properly formatted!"
            );
        }

        // Validate against schema
        $dom = new DOMDocument();
        $dom->loadXML($xml->asXML());

        // Check if the XML file is properly validated with the XSD
        if (@$dom->schemaValidate($xsdFile) === false) {
            fail(
                $title,
                "The file <b>$xmlFile</b> is not properly validated with the <b>XSD</b>!"
            );
        }

        return $xml;
    }
