<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CSRF Protection for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\security;

defined('MOODLE_INTERNAL') || die();

/**
 * Class csrf_protection
 */
class csrf_protection {
    
    /**
     * Generate a CSRF token for the current session
     *
     * @param string $action The action this token is for
     * @return string The CSRF token
     */
    public function generate_token($action = '') {
        // Create a unique token based on session ID, action, and a secret
        $sessionid = session_id();
        $secret = $this->get_csrf_secret();
        $timestamp = time();
        
        // Combine the values and hash them
        $token_data = $sessionid . '|' . $action . '|' . $timestamp . '|' . $secret;
        $token = hash('sha256', $token_data);
        
        // Store the token in session with timestamp
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = array();
        }
        
        $_SESSION['csrf_tokens'][$token] = array(
            'action' => $action,
            'timestamp' => $timestamp,
            'expires' => $timestamp + 3600 // Expires in 1 hour
        );
        
        return $token;
    }
    
    /**
     * Verify a CSRF token (alias for validate_token)
     *
     * @param string $token The token to verify
     * @param string $action The action this token is for
     * @return bool True if valid, false otherwise
     */
    public function verify_token($token, $action = '') {
        return $this->validate_token($token, $action);
    }

    /**
     * Validate a CSRF token
     *
     * @param string $token The token to validate
     * @param string $action The action this token is for
     * @return bool True if valid, false otherwise
     */
    public function validate_token($token, $action = '') {
        // Check if the token exists in session
        if (empty($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        $token_data = $_SESSION['csrf_tokens'][$token];
        
        // Check if the token has expired
        if ($token_data['expires'] < time()) {
            $this->remove_token($token);
            return false;
        }
        
        // Check if the action matches (if specified)
        if (!empty($action) && $token_data['action'] !== $action) {
            return false;
        }
        
        // Token is valid, remove it to prevent reuse
        $this->remove_token($token);
        
        return true;
    }
    
    /**
     * Remove a CSRF token from the session
     *
     * @param string $token The token to remove
     */
    private function remove_token($token) {
        if (isset($_SESSION['csrf_tokens'][$token])) {
            unset($_SESSION['csrf_tokens'][$token]);
        }
    }
    
    /**
     * Get the CSRF secret key
     *
     * @return string The CSRF secret
     */
    private function get_csrf_secret() {
        global $CFG;
        
        // Use Moodle's secret passphrase as the base for CSRF secret
        return $CFG->secretpassphrase ?? 'default_csrf_secret';
    }
    
    /**
     * Generate and output a hidden form field with CSRF token
     *
     * @param string $action The action this token is for
     * @return string HTML for the hidden input field
     */
    public function get_hidden_field($action = '') {
        $token = $this->generate_token($action);
        return '<input type="hidden" name="secureotp_csrf_token" value="' . htmlspecialchars($token) . '" />';
    }
    
    /**
     * Validate CSRF token from a form submission
     *
     * @param string $action The action to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_form_submission($action = '') {
        $token = $_POST['secureotp_csrf_token'] ?? '';
        
        if (empty($token)) {
            return false;
        }
        
        return $this->validate_token($token, $action);
    }
    
    /**
     * Clean up expired CSRF tokens from session
     */
    public function cleanup_expired_tokens() {
        if (empty($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $current_time = time();
        $tokens_to_remove = array();
        
        foreach ($_SESSION['csrf_tokens'] as $token => $data) {
            if ($data['expires'] < $current_time) {
                $tokens_to_remove[] = $token;
            }
        }
        
        foreach ($tokens_to_remove as $token) {
            unset($_SESSION['csrf_tokens'][$token]);
        }
    }
    
    /**
     * Generate a token and add it to the URL as a parameter
     *
     * @param string $url The URL to append the token to
     * @param string $action The action this token is for
     * @return string The URL with the token appended
     */
    public function add_token_to_url($url, $action = '') {
        $token = $this->generate_token($action);
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'secureotp_csrf_token=' . urlencode($token);
    }
    
    /**
     * Validate a token from URL parameters
     *
     * @param string $action The action to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_url_token($action = '') {
        $token = $_GET['secureotp_csrf_token'] ?? '';
        
        if (empty($token)) {
            return false;
        }
        
        return $this->validate_token($token, $action);
    }
}