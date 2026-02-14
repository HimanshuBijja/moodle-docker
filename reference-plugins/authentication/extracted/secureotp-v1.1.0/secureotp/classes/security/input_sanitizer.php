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
 * Input Sanitizer for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\security;

defined('MOODLE_INTERNAL') || die();

/**
 * Class input_sanitizer
 */
class input_sanitizer {
    
    /**
     * Sanitize a phone number
     *
     * @param string $phonenumber The phone number to sanitize
     * @return string The sanitized phone number
     */
    public function sanitize_phone_number($phonenumber) {
        // Remove all non-digit characters except +
        $sanitized = preg_replace('/[^0-9+]/', '', $phonenumber);
        
        // Ensure it starts with + if international format is expected
        if (substr($sanitized, 0, 1) !== '+' && strlen($sanitized) > 10) {
            // If it looks like an international number without +, add it
            if (strlen($sanitized) >= 10) {
                $sanitized = '+' . $sanitized;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate a phone number format
     *
     * @param string $phonenumber The phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_phone_number($phonenumber) {
        // Basic validation: allow + followed by 10-15 digits
        return preg_match('/^\+?[1-9]\d{9,14}$/', $this->sanitize_phone_number($phonenumber));
    }
    
    /**
     * Sanitize an email address
     *
     * @param string $email The email address to sanitize
     * @return string The sanitized email address
     */
    public function sanitize_email($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Validate an email address
     *
     * @param string $email The email address to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitize an identifier (employee ID, mobile, or email)
     *
     * @param string $identifier The identifier to sanitize
     * @return string The sanitized identifier
     */
    public function sanitize_identifier($identifier) {
        // Trim whitespace
        $sanitized = trim($identifier);

        // Remove any HTML tags
        $sanitized = strip_tags($sanitized);

        // Allow: letters, numbers, @, ., -, +, _
        // This covers employee IDs (MH001234), emails (user@example.com), and mobiles (+919876543210)
        $sanitized = preg_replace('/[^a-zA-Z0-9@.+_-]/', '', $sanitized);

        return $sanitized;
    }

    /**
     * Sanitize a username
     *
     * @param string $username The username to sanitize
     * @return string The sanitized username
     */
    public function sanitize_username($username) {
        // Allow letters, numbers, underscores, hyphens, and dots
        return preg_replace('/[^a-zA-Z0-9_.-]/', '', trim($username));
    }
    
    /**
     * Validate a username format
     *
     * @param string $username The username to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_username($username) {
        // At least 3 characters, max 100, alphanumeric and allowed symbols
        return preg_match('/^[a-zA-Z0-9_.-]{3,100}$/', $this->sanitize_username($username));
    }
    
    /**
     * Sanitize an OTP code
     *
     * @param string $otp The OTP code to sanitize
     * @return string The sanitized OTP code
     */
    public function sanitize_otp($otp) {
        // Only allow digits
        return preg_replace('/[^0-9]/', '', trim($otp));
    }
    
    /**
     * Validate an OTP code format
     *
     * @param string $otp The OTP code to validate
     * @param int $expected_length Expected length of the OTP
     * @return bool True if valid, false otherwise
     */
    public function validate_otp($otp, $expected_length = 6) {
        $clean_otp = $this->sanitize_otp($otp);
        return preg_match('/^[0-9]{' . $expected_length . '}$/', $clean_otp);
    }
    
    /**
     * Sanitize a URL
     *
     * @param string $url The URL to sanitize
     * @return string The sanitized URL
     */
    public function sanitize_url($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }
    
    /**
     * Validate a URL
     *
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Sanitize text input (removes HTML tags)
     *
     * @param string $text The text to sanitize
     * @return string The sanitized text
     */
    public function sanitize_text($text) {
        return htmlspecialchars(strip_tags(trim($text)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize HTML input (allows only safe tags)
     *
     * @param string $html The HTML to sanitize
     * @param array $allowed_tags Allowed HTML tags
     * @return string The sanitized HTML
     */
    public function sanitize_html($html, $allowed_tags = array('p', 'br', 'strong', 'em', 'ul', 'ol', 'li')) {
        // First strip all tags not in the allowed list
        $allowed_tag_string = '<' . implode('><', $allowed_tags) . '>';
        $stripped = strip_tags($html, $allowed_tag_string);
        
        // Then escape any remaining special characters
        return htmlspecialchars($stripped, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize SQL LIKE parameter to prevent SQL injection
     *
     * @param string $input The input to sanitize for LIKE operations
     * @return string The sanitized input
     */
    public function sanitize_like_parameter($input) {
        // Escape LIKE wildcards
        $escaped = str_replace(array('%', '_'), array('\\%', '\\_'), $input);
        return $this->sanitize_text($escaped);
    }
    
    /**
     * Sanitize an array of inputs recursively
     *
     * @param array $input The input array to sanitize
     * @param array $rules Rules for sanitizing specific keys
     * @return array The sanitized array
     */
    public function sanitize_array($input, $rules = array()) {
        $output = array();
        
        foreach ($input as $key => $value) {
            $clean_key = $this->sanitize_text($key);
            
            if (is_array($value)) {
                $output[$clean_key] = $this->sanitize_array($value, $rules);
            } else {
                // Apply specific sanitization based on key name or rules
                if (isset($rules[$key])) {
                    $output[$clean_key] = $this->$rules[$key]($value);
                } else {
                    // Default sanitization
                    $output[$clean_key] = $this->sanitize_text($value);
                }
            }
        }
        
        return $output;
    }
    
    /**
     * Validate an array of inputs against a set of rules
     *
     * @param array $input The input array to validate
     * @param array $rules Validation rules for each key
     * @return array Array of validation errors, empty if all valid
     */
    public function validate_array($input, $rules) {
        $errors = array();
        
        foreach ($rules as $field => $validation_rules) {
            $value = $input[$field] ?? null;
            
            foreach ($validation_rules as $rule) {
                $is_valid = false;
                
                switch ($rule) {
                    case 'required':
                        $is_valid = !empty($value);
                        break;
                    case 'email':
                        $is_valid = $this->validate_email($value);
                        break;
                    case 'phone':
                        $is_valid = $this->validate_phone_number($value);
                        break;
                    case 'username':
                        $is_valid = $this->validate_username($value);
                        break;
                    case 'url':
                        $is_valid = $this->validate_url($value);
                        break;
                    default:
                        // Custom validation rule
                        if (method_exists($this, $rule)) {
                            $is_valid = $this->$rule($value);
                        }
                        break;
                }
                
                if (!$is_valid) {
                    $errors[$field][] = $rule;
                }
            }
        }
        
        return $errors;
    }
}