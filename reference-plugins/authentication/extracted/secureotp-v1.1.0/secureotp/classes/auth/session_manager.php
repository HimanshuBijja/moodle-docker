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
 * Session Manager for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\auth;

defined('MOODLE_INTERNAL') || die();

/**
 * Class session_manager
 */
class session_manager {
    
    /**
     * Create a new JWT session token for the authenticated user
     *
     * @param int $userid The user ID
     * @return string The JWT token
     */
    public function create_session_token($userid) {
        global $CFG;
        
        // Include JWT library
        require_once($CFG->libdir . '/jwt/autoload.php');
        
        $config = get_config('auth_secureotp');
        
        // Set token payload
        $payload = array(
            'iss' => $CFG->wwwroot, // Issuer
            'aud' => $CFG->wwwroot, // Audience
            'iat' => time(), // Issued at
            'exp' => time() + (3600 * 24), // Expiration (24 hours)
            'sub' => $userid, // Subject (user ID)
            'type' => 'auth' // Token type
        );
        
        // Get secret key from config
        $secretkey = !empty($config->jwt_secret_key) ? $config->jwt_secret_key : $CFG->secretpassphrase;
        
        // Encode the token
        $token = \Firebase\JWT\JWT::encode($payload, $secretkey, 'HS256');
        
        return $token;
    }
    
    /**
     * Validate a JWT session token
     *
     * @param string $token The JWT token
     * @return array|null Array with user info if valid, null if invalid
     */
    public function validate_session_token($token) {
        global $CFG;
        
        // Include JWT library
        require_once($CFG->libdir . '/jwt/autoload.php');
        
        $config = get_config('auth_secureotp');
        $secretkey = !empty($config->jwt_secret_key) ? $config->jwt_secret_key : $CFG->secretpassphrase;
        
        try {
            // Decode the token
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secretkey, 'HS256'));
            
            // Check if token is expired
            if (property_exists($decoded, 'exp') && $decoded->exp < time()) {
                return null;
            }
            
            // Return user ID from token
            return array(
                'userid' => $decoded->sub,
                'issued_at' => $decoded->iat,
                'expires_at' => $decoded->exp
            );
        } catch (\Exception $e) {
            // Token is invalid
            return null;
        }
    }
    
    /**
     * Refresh an existing session token
     *
     * @param string $token The existing token
     * @return string|null New token if refreshable, null otherwise
     */
    public function refresh_session_token($token) {
        // Validate the existing token first
        $valid = $this->validate_session_token($token);
        
        if (!$valid) {
            return null;
        }
        
        // Create a new token with extended expiration
        return $this->create_session_token($valid['userid']);
    }
    
    /**
     * Destroy a session token (logout)
     *
     * @param string $token The token to destroy
     * @return bool True if successful
     */
    public function destroy_session_token($token) {
        // In a real implementation, you might add the token to a blacklist
        // For now, we just return true since JWTs are stateless
        return true;
    }
    
    /**
     * Store session data in cache
     *
     * @param string $sessionid The session ID
     * @param array $data The session data
     * @param int $ttl Time to live in seconds
     * @return bool True if successful
     */
    public function store_session_data($sessionid, $data, $ttl = 3600) {
        // Use Moodle's cache API
        $cache = \cache::make('auth_secureotp', 'sessions');
        return $cache->set($sessionid, $data, $ttl);
    }
    
    /**
     * Retrieve session data from cache
     *
     * @param string $sessionid The session ID
     * @return mixed Session data or false if not found
     */
    public function get_session_data($sessionid) {
        $cache = \cache::make('auth_secureotp', 'sessions');
        return $cache->get($sessionid);
    }
    
    /**
     * Delete session data from cache
     *
     * @param string $sessionid The session ID
     * @return bool True if successful
     */
    public function delete_session_data($sessionid) {
        $cache = \cache::make('auth_secureotp', 'sessions');
        return $cache->delete($sessionid);
    }
}