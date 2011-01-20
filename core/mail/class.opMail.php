<?php
defined('_OP') or die('Access denied');
/**
 *  Copyright (C) 2009 Lars Boldt
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class opMail {
    private $mailRecipients     = array();
    private $mailCCRecipients   = array();
    private $mailBCCRecipients  = array();
    private $mailFrom           = null;
    private $mailSubject        = 'Untitled';
    private $mailMessage        = null;

    public function __construct() {
        
    }

    public function addRecipient(opMailRecipient $recipient) {
        $this->mailRecipients[] = $recipient;
    }

    public function addCCRecipient(opMailRecipient $recipient) {
        $this->mailCCRecipients[] = $recipient;
    }

    public function addBCCRecipient(opMailRecipient $recipient) {
        $this->mailBCCRecipients[] = $recipient;
    }

    public function setFrom(opMailRecipient $recipient) {
        $this->mailFrom = $recipient;
    }

    public function setSubject($subject) {
        $this->mailSubject = $subject;
    }

    public function setMessage($message) {
        $this->mailMessage = $message;
    }

    public function sendMail() {
        $headers    = "MIME-Version: 1.0\n";
        $headers   .= "Content-type: text/plain; charset=UTF-8\n";
        $headers   .= "From:".$this->mailFrom->getRecipient()."\n";
        $headers   .= "Return-Path:".$this->mailFrom->getRecipient()."\n";
        if (count($this->mailCCRecipients) > 0) {
            $headers   .= "Cc:".$this->buildRecipientList($this->mailCCRecipients)."\n";
        }
        if (count($this->mailBCCRecipients) > 0) {
            $headers   .= "Bcc:".$this->buildRecipientList($this->mailBCCRecipients)."\n";
        }
        mb_language("uni");
        return mb_send_mail($this->buildRecipientList($this->mailRecipients), $this->mailSubject, $this->mailMessage, $headers);
    }

    protected function buildRecipientList(array $recipientList) {
        $recipients = "";
        foreach ($recipientList as $recipient) {
            $recipients .= $recipient->getRecipient().",";
        }
        return substr($recipients,0,strlen($recipients)-1);
    }
}
?>