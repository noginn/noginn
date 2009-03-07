<?php

/**
 * Sends a file for download
 *
 * @category Noginn
 * @copyright Copyright (c) 2009 Tom Graham (http://www.noginn.com)
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Noginn_Controller_Action_Helper_SendFile extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Proxy method for sendFile
     *
     * @param string $path Path to the file
     * @param string $type The mime-type of the file
     * @param string $filename The filename to send the file as, if null then use the base name of the path
     * @param string $disposition Whether the file should be sent inline or as an attachment
     * @param bool $useXSendfile Whether to use the X-Sendfile response header
     * @return bool Whether the headers and file were sent
     */
    public function direct($path, $type, $filename = null, $disposition = 'attachment', $useXSendfile = false)
    {
        return $this->sendFile($path, $type, $filename, $disposition, $useXSendfile);
    }

    /**
     * Send a file for download
     *
     * @param string $path Path to the file
     * @param string $type The mime-type of the file
     * @param string $filename The filename to send the file as, if null then use the base name of the path
     * @param string $disposition Whether the file should be sent inline or as an attachment
     * @param bool $useXSendfile Whether to use the X-Sendfile response header
     * @return bool Whether the headers and file were sent
     */
    public function sendFile($path, $type, $filename = null, $disposition = 'attachment', $useXSendfile = false)
    {
        $response = $this->getResponse();
        
        if (!is_readable($path) || !$response->canSendHeaders()) {
            return false;
        }

        // Set the file name
        if ($filename !== null) {
            $filename = $filename;
        } else {
            $filename = basename($path);
        }

        // Set the content disposition (can only be attachment or inline)
        if ($disposition != 'attachment') {
            $disposition = 'inline';
        }
        
        $response->setHeader('Content-Type', $type, true);
        $response->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"', true);

        // Do we want to use the X-Sendfile header or stream the file
        if ($useXSendfile) {
            $response->setHeader('X-Sendfile', $path);
            $response->sendHeaders();
            return true;
        }
        
        $response->setHeader('Content-Length', filesize($path), true);
        $response->sendHeaders();

        readfile($path);
        return true;
    }

    /**
     * Send file data as a download
     *
     * @param string $data The data to send
     * @param string $type The mime-type of the file
     * @param string $filename The filename to send the file as, if null then use the base name of the path
     * @param string $disposition Whether the file should be sent inline or as an attachment
     * @return bool Whether the headers and file were sent
     */
    public function sendData($data, $type, $filename, $disposition = 'attachment')
    {
        $response = $this->getResponse();
        
        if (empty($data) || !$response->canSendHeaders()) {
            return false;
        }

        // Set the content disposition (can only be attachment or inline)
        if ($disposition != 'attachment') {
            $disposition = 'inline';
        }

        $response->setHeader('Content-Type', $type, true);
        $response->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"', true);
        $response->setHeader('Content-Length', strlen($data), true);
        $response->sendHeaders();

        echo $data;
        return true;
    }
}
