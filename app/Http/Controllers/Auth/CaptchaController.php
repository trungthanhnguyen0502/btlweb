<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    /**
     * Hash Algorithms
     *
     * @var integer
     */

    public const HASH_ALGO_NONE = 0;
    public const HASH_ALGO_MD5 = 1;
    public const HASH_ALGO_SHA1 = 2;
    public const HASH_ALGO_MIXED_MD5_SHA1 = 12;
    public const HASH_ALGO_MIXED_SHA1_MD5 = 21;
    /**
     * The image resource
     * @var object|resource
     */

    protected $image;
    /**
     * Captcha credits
     * The bottom text of captcha
     *
     * @var string
     */

    protected $credits;
    /**
     * JPEG Quality
     *
     * @var integer
     */

    protected $jpeg_quality;
    /**
     * Captcha keystring
     * @var string
     */

    protected $keystring;

    /**
     * Class constructor
     */

    public function __construct()
    {
        $this->image = null;
        $this->keystring = '';
        $this->set_jpeg_quality(98);
    }

    public function set_jpeg_quality($jpeg_quality = 98)
    {
        $this->jpeg_quality = $jpeg_quality;
    }

    public function set_credits($credits = '')
    {
        $this->credits = $credits;
    }

    public function index()
    {
        $this->create_captcha();
        session(['captcha' => $this->get_keystring(CaptchaController::HASH_ALGO_MD5)]);
        $this->output();
    }

    /**
     * Generates keystring and image
     *
     * @return  resource
     */

    public function create_captcha()
    {
        ////////////////////////////////////////////////////////////
        // Настройки CAPTCHA                                      //
        ////////////////////////////////////////////////////////////
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
        // Just use hexadecimal characters
        $allowed_symbols = '0123456789abcdef';
        // Source font directory
        $fontsdir = '../resources/assets/images/captcha';
        $length = mt_rand(6, 8);
        $width = ($length - 5) * 10 + 100;
        $height = 50;
        $fluctuation_amplitude = 5;
        $no_spaces = true;
        if ($this->credits) {
            $show_credits = True;
        } else {
            $show_credits = False;
        }

        $credits = $this->credits;

        $foreground_color = array(
            mt_rand(0, 100),
            mt_rand(0, 100),
            mt_rand(0, 100)
        );

        $background_color = array(
            mt_rand(200, 255),
            mt_rand(200, 255),
            mt_rand(200, 255)
        );

        ////////////////////////////////////////////////////////////
        $fonts = array();
        // Get the font dir path
        // $fontsdir_absolute = dirname(__FILE__) . '/' . $fontsdir;
        $fontsdir_absolute = $fontsdir;

        if (($handle = opendir($fontsdir_absolute)) !== false) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match('/\.png$/i', $file)) {
                    $fonts[] = $fontsdir_absolute . '/' . $file;
                }
            }
            closedir($handle);
        }

        $alphabet_length = strlen($alphabet);

        do {
            // generating random keystring
            while (true) {
                $this->keystring = '';
                for ($i = 0; $i < $length; $i++) {
                    $this->keystring .= $allowed_symbols{mt_rand(0, strlen($allowed_symbols) - 1)};
                }
                if (!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $this->keystring))
                    break;
            }
            $font_file = $fonts[mt_rand(0, count($fonts) - 1)];
            $font = imagecreatefrompng($font_file);
            imagealphablending($font, true);
            $fontfile_width = imagesx($font);
            $fontfile_height = imagesy($font) - 1;
            $font_metrics = array();
            $symbol = 0;
            $reading_symbol = false;
            // loading font
            for ($i = 0; $i < $fontfile_width && $symbol < $alphabet_length; $i++) {
                $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;
                if (!$reading_symbol && !$transparent) {
                    $font_metrics[$alphabet{$symbol}] = array('start' => $i);
                    $reading_symbol = true;
                    continue;
                }
                if ($reading_symbol && $transparent) {
                    $font_metrics[$alphabet{$symbol}]['end'] = $i;
                    $reading_symbol = false;
                    $symbol++;
                    continue;
                }
            }
            $img = imagecreatetruecolor($width, $height);
            imagealphablending($img, true);
            $white = imagecolorallocate($img, 255, 255, 255);
            imagefilledrectangle($img, 0, 0, $width - 1, $height - 1, $white);
            // draw text
            $x = 1;
            for ($i = 0; $i < $length; $i++) {
                $m = $font_metrics[$this->keystring{$i}];
                $y = mt_rand(-$fluctuation_amplitude, $fluctuation_amplitude) + ($height - $fontfile_height) / 2 + 2;
                if ($no_spaces) {
                    $shift = 0;
                    if ($i > 0) {
                        $shift = 10000;
                        for ($sy = 7; $sy < $fontfile_height - 20; $sy += 1) {
                            for ($sx = $m['start'] - 1; $sx < $m['end']; $sx += 1) {
                                $rgb = imagecolorat($font, $sx, $sy);
                                $opacity = $rgb >> 24;
                                if ($opacity < 127) {
                                    $left = $sx - $m['start'] + $x;
                                    $py = $sy + $y;
                                    if ($py > $height)
                                        break;
                                    for ($px = min($left, $width - 1); $px > $left - 12 && $px >= 0; $px -= 1) {
                                        $color = imagecolorat($img, $px, $py) & 0xff;
                                        if ($color + $opacity < 190) {
                                            if ($shift > $left - $px) {
                                                $shift = $left - $px;
                                            }
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if ($shift == 10000) {
                            $shift = mt_rand(4, 6);
                        }
                    }
                } else {
                    $shift = 1;
                }
                imagecopy($img, $font, $x - $shift, $y, $m['start'], 1, $m['end'] - $m['start'], $fontfile_height);
                $x += $m['end'] - $m['start'] - $shift;
            }
        } while ($x >= $width - 10); // while not fit in canvas
        $center = $x / 2;
        // credits. To remove, see configuration file
        $this->image = imagecreatetruecolor($width, $height + ($show_credits ? 12 : 0));
        $foreground = imagecolorallocate($this->image, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
        $background = imagecolorallocate($this->image, $background_color[0], $background_color[1], $background_color[2]);
        imagefilledrectangle($this->image, 0, 0, $width - 1, $height - 1, $background);
        imagefilledrectangle($this->image, 0, $height, $width - 1, $height + 12, $foreground);
        $credits = empty($credits) ? $_SERVER['HTTP_HOST'] : $credits;
        imagestring($this->image, 2, $width / 2 - imagefontwidth(2) * strlen($credits) / 2, $height - 2, $credits, $background);
        // periods
        $rand1 = mt_rand(750000, 1200000) / 10000000;
        $rand2 = mt_rand(750000, 1200000) / 10000000;
        $rand3 = mt_rand(750000, 1200000) / 10000000;
        $rand4 = mt_rand(750000, 1200000) / 10000000;
        // phases
        $rand5 = mt_rand(0, 31415926) / 10000000;
        $rand6 = mt_rand(0, 31415926) / 10000000;
        $rand7 = mt_rand(0, 31415926) / 10000000;
        $rand8 = mt_rand(0, 31415926) / 10000000;
        // amplitudes
        $rand9 = mt_rand(330, 420) / 110;
        $rand10 = mt_rand(330, 450) / 110;

        //wave distortion
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $sx = $x + (sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6)) * $rand9 - $width / 2 + $center + 1;
                $sy = $y + (sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8)) * $rand10;
                if ($sx < 0 || $sy < 0 || $sx >= $width - 1 || $sy >= $height - 1) {
                    continue;
                } else {
                    $color = imagecolorat($img, $sx, $sy) & 0xFF;
                    $color_x = imagecolorat($img, $sx + 1, $sy) & 0xFF;
                    $color_y = imagecolorat($img, $sx, $sy + 1) & 0xFF;
                    $color_xy = imagecolorat($img, $sx + 1, $sy + 1) & 0xFF;
                }
                if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255) {
                    continue;
                } else if ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
                    $newred = $foreground_color[0];
                    $newgreen = $foreground_color[1];
                    $newblue = $foreground_color[2];
                } else {
                    $frsx = $sx - floor($sx);
                    $frsy = $sy - floor($sy);
                    $frsx1 = 1 - $frsx;
                    $frsy1 = 1 - $frsy;

                    $newcolor = ($color * $frsx1 * $frsy1 + $color_x * $frsx * $frsy1 + $color_y * $frsx1 * $frsy + $color_xy * $frsx * $frsy);
                    if ($newcolor > 255)
                        $newcolor = 255;
                    $newcolor = $newcolor / 255;
                    $newcolor0 = 1 - $newcolor;
                    $newred = $newcolor0 * $foreground_color[0] + $newcolor * $background_color[0];
                    $newgreen = $newcolor0 * $foreground_color[1] + $newcolor * $background_color[1];
                    $newblue = $newcolor0 * $foreground_color[2] + $newcolor * $background_color[2];
                }
                imagesetpixel($this->image, $x, $y, imagecolorallocate($this->image, $newred, $newgreen, $newblue));
            }
        }
    }

    /**
     * Get keystring after hashing by an algorithm
     *
     * @param   integer
     * @return  string
     */

    public function get_keystring($hash_algo = CaptchaController::HASH_ALGO_NONE)
    {

        switch ($hash_algo) {
            case CaptchaController::HASH_ALGO_MD5:
                return md5($this->keystring);
                break;
            case CaptchaController::HASH_ALGO_SHA1:
                return sha1($this->keystring);
                break;
            case CaptchaController::HASH_ALGO_MIXED_MD5_SHA1:
                return md5(sha1($this->keystring));
                break;
            case CaptchaController::HASH_ALGO_MIXED_SHA1_MD5:
                return sha1(md5($this->keystring));
            case CaptchaController::HASH_ALGO_NONE:
            default:
                return $this->keystring;
                break;
        }
    }

    public function output($type = IMAGETYPE_PNG)
    {
        ob_start();
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
        // Output file by image type
        switch ($type) {
            case IMAGETYPE_PNG:
                imagePng($this->image);
                break;
            case IMAGETYPE_GIF:
                imageGif($this->image);
                break;
            case IMAGETYPE_JPEG:
            default:
                imageJpeg($this->image, null, $this->jpeg_quality);
                break;
        }
        // Set MIME Type for Image
        $mime_type = image_type_to_mime_type($type);
        header("Content-Type: {$mime_type}");
        // Set the output filename
        $filename = md5($_SERVER['REQUEST_TIME']);
        $filename .= image_type_to_extension($type, True);
        // Set Content-Disposition
        header("Content-Disposition: inline; filename={$filename}");
        // File size
        $length = ob_get_length();
        header("Content-Length: {$length}");
        // Flush image data
        ob_end_flush();
    }

    public function key(Request $request)
    {
        $key = session('captcha');
        if ($key == null) {
            $this->create_captcha();
            session(['captcha' => $this->get_keystring(CaptchaController::HASH_ALGO_MD5)]);
            return [
                'key' => session('captcha')
            ];
        } else {
            return [
                'key' => $key
            ];
        }
    }
}
