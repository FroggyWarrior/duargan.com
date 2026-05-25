<?php
namespace App\Utils;

/**
 * Utility class for handling Time-based One-Time Password (TOTP) authentication.
 */
class TOTPAuthenticator {
    /**
     * @var int Length of the generated OTP code.
     */
    protected $_codeLength = 6;

    /**
     * Generates a random Base32 secret key.
     * 
     * @param int $secretLength Length of the secret.
     * @return string The generated secret.
     */
    public function createSecret($secretLength = 16) {
        $validChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        $secret = "";
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Calculates the OTP code for a given secret at a specific time slice.
     * 
     * @param string $secret The Base32 secret.
     * @param int|null $timeSlice The time slice (30s intervals). Defaults to current time.
     * @return string The 6-digit OTP code.
     */
    public function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretKey = $this->_base32Decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hmac = hash_hmac('SHA1', $time, $secretKey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1] & 0x7FFFFFFF;
        $module = pow(10, $this->_codeLength);
        return str_pad($value % $module, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Verifies if a provided code is valid for a secret.
     * 
     * @param string $secret The Base32 secret.
     * @param string $code The code provided by the user.
     * @param int $discrepancy Allowed time window (number of 30s slices before/after).
     * @param int|null $currentTimeSlice The time slice to check against.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null) {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }
        if (strlen($code) != 6) return false;

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode == $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generates a standard otpauth:// URL for QR code generation.
     * 
     * @param string $name Account name/username.
     * @param string $secret The Base32 secret.
     * @param string|null $title The issuer name.
     * @return string The otpauth URL.
     */
    public function getOTPAuthUrl($name, $secret, $title = null) {
        return 'otpauth://totp/'.urlencode($title).':'.urlencode($name).'?secret='.$secret.'&issuer='.urlencode($title);
    }

    /**
     * Decodes a Base32 encoded string.
     * @param string $secret The Base32 string.
     * @return string The binary decoded string.
     */
    private function _base32Decode($secret) {
        if (empty($secret)) return '';
        $base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        $base32charsFlipped = array_flip(str_split($base32chars));
        $output = "";
        $i = 0;
        $buffer = 0;
        $bufferLength = 0;
        while ($i < strlen($secret)) {
            $char = $secret[$i++];
            if (!isset($base32charsFlipped[$char])) continue;
            $buffer <<= 5;
            $buffer += $base32charsFlipped[$char];
            $bufferLength += 5;
            if ($bufferLength >= 8) {
                $output .= chr(($buffer >> ($bufferLength - 8)) & 0xFF);
                $bufferLength -= 8;
            }
        }
        return $output;
    }
}