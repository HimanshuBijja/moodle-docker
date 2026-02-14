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
 * Encryption utilities for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\security;

defined('MOODLE_INTERNAL') || die();

/**
 * Class encryption
 */
class encryption {
    
    /**
     * Encrypt data using AES-256-CBC
     *
     * @param string $data The data to encrypt
     * @param string $key The encryption key (will be derived if not provided)
     * @return string The encrypted data (base64 encoded)
     */
    public function encrypt($data, $key = null) {
        if (is_null($key)) {
            $key = $this->get_encryption_key();
        }
        
        // Generate a random IV
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        
        // Encrypt the data
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($encrypted === false) {
            throw new \moodle_exception('encryption_error', 'auth_secureotp');
        }
        
        // Return base64 encoded IV + encrypted data
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data using AES-256-CBC
     *
     * @param string $encrypteddata The encrypted data (base64 encoded)
     * @param string $key The encryption key (will be derived if not provided)
     * @return string The decrypted data
     */
    public function decrypt($encrypteddata, $key = null) {
        if (is_null($key)) {
            $key = $this->get_encryption_key();
        }
        
        // Decode the base64 data
        $data = base64_decode($encrypteddata);
        
        if ($data === false) {
            throw new \moodle_exception('decryption_error_invalid_data', 'auth_secureotp');
        }
        
        // Extract IV and encrypted data
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivlen);
        $encrypted = substr($data, $ivlen);
        
        // Decrypt the data
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            throw new \moodle_exception('decryption_error', 'auth_secureotp');
        }
        
        return $decrypted;
    }
    
    /**
     * Get the encryption key from configuration or generate one
     *
     * @return string The encryption key
     */
    private function get_encryption_key() {
        global $CFG;
        
        $config = get_config('auth_secureotp');
        
        // Check if we have a specific encryption key in config
        if (!empty($config->encryption_key)) {
            // Use the provided key (should be 32 bytes for AES-256)
            $key = base64_decode($config->encryption_key);
            if (strlen($key) === 32) {
                return $key;
            }
        }
        
        // Fallback to using Moodle's secret passphrase
        $key = $CFG->secretpassphrase ?? 'fallback_default_key';
        
        // Ensure the key is exactly 32 bytes for AES-256
        return str_pad(substr($key, 0, 32), 32, "\0");
    }
    
    /**
     * Hash sensitive data using a salted hash
     *
     * @param string $data The data to hash
     * @param string $salt Optional salt (generated if not provided)
     * @return string The hashed data in format: salt:hash
     */
    public function hash_sensitive_data($data, $salt = null) {
        if (is_null($salt)) {
            $salt = bin2hex(random_bytes(16)); // 16-byte salt
        }
        
        // Use SHA-256 with salt
        $hash = hash('sha256', $salt . $data);
        
        return $salt . ':' . $hash;
    }
    
    /**
     * Verify a hashed value against the original data
     *
     * @param string $data The original data
     * @param string $hashed The hashed value in format: salt:hash
     * @return bool True if the data matches the hash
     */
    public function verify_hashed_data($data, $hashed) {
        $parts = explode(':', $hashed, 2);
        
        if (count($parts) !== 2) {
            return false;
        }
        
        $salt = $parts[0];
        $expected_hash = $parts[1];
        
        // Recalculate the hash with the provided salt
        $actual_hash = hash('sha256', $salt . $data);
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($expected_hash, $actual_hash);
    }
    
    /**
     * Generate a secure random string
     *
     * @param int $length The length of the string to generate
     * @param string $chars The characters to use (defaults to alphanumeric)
     * @return string The random string
     */
    public function generate_random_string($length = 32, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        $charlen = strlen($chars);
        $randomstring = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomstring .= $chars[random_int(0, $charlen - 1)];
        }
        
        return $randomstring;
    }
    
    /**
     * Encrypt a user's phone number for storage
     *
     * @param string $phonenumber The phone number to encrypt
     * @return string The encrypted phone number
     */
    public function encrypt_phone_number($phonenumber) {
        return $this->encrypt($phonenumber);
    }
    
    /**
     * Decrypt a user's phone number
     *
     * @param string $encryptednumber The encrypted phone number
     * @return string The decrypted phone number
     */
    public function decrypt_phone_number($encryptednumber) {
        return $this->decrypt($encryptednumber);
    }
    
    /**
     * Encrypt a user's TOTP secret
     *
     * @param string $secret The TOTP secret to encrypt
     * @return string The encrypted secret
     */
    public function encrypt_totp_secret($secret) {
        return $this->encrypt($secret);
    }
    
    /**
     * Decrypt a user's TOTP secret
     *
     * @param string $encryptedsecret The encrypted TOTP secret
     * @return string The decrypted secret
     */
    public function decrypt_totp_secret($encryptedsecret) {
        return $this->decrypt($encryptedsecret);
    }
    
    /**
     * Generate a secure encryption key and return it in a format suitable for config
     *
     * @return string The base64-encoded encryption key
     */
    public function generate_new_encryption_key() {
        return base64_encode(random_bytes(32)); // 32 bytes for AES-256
    }
}