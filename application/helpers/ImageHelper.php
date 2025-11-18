<?php

namespace IndependentNiche\application\helpers;

use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * ImageHelper class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 *
 */
class ImageHelper
{

	const DOWNLOAD_TIMEOUT = 7;
	const USERAGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Safari/605.1.15';

	public static function setFeaturedImage($post_id, $image_uri, $title = '', $slug = '')
	{
		$image_file = self::saveImgLocaly($image_uri, $title, $slug);

		if (!$image_file)
			return false;

		return self::attachThumbnail($image_file, $post_id, $title);
	}

	public static function attachThumbnail($image_file, $post_id, $title = '')
	{
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$title = \sanitize_text_field($title);
		$filetype = \wp_check_filetype(basename($image_file), null);
		$attachment = array(
			'guid' => $image_file,
			'post_mime_type' => $filetype['type'],
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = \wp_insert_attachment($attachment, $image_file, $post_id);
		$attach_data = \wp_generate_attachment_metadata($attach_id, $image_file);

		\wp_update_attachment_metadata($attach_id, $attach_data);
		if ($title)
			\update_post_meta($attach_id, '_wp_attachment_image_alt', $title);

		return \set_post_thumbnail($post_id, $attach_id);
	}

	public static function saveImgLocaly($img_uri, $title = '', $slug = '', $check_image_type = true)
	{
		if ($slug)
			$newfilename = $slug;
		elseif ($title)
		{
			$newfilename = \wp_trim_words($title, 8, '');
			$newfilename = str_replace(' ', '-', $newfilename);
		}
		$newfilename = preg_replace('/[^a-zA-Z0-9\-]/', '', $newfilename);
		$newfilename = strtolower($newfilename);
		if (!$newfilename)
			$newfilename = time();

		$uploads = \wp_upload_dir();

		if ($filepath = self::downloadImg($img_uri, $uploads['path'], $newfilename, null, $check_image_type))
			return $filepath;
		else
			return false;
	}

	static public function getUserAgent($img_uri)
	{
		return \apply_filters('tmniche_image_user_agent', self::USERAGENT, $img_uri);
	}

	public static function downloadImg($img_uri, $save_dir, $file_name, $file_ext = null, $check_image_type = true)
	{
		$response = \wp_remote_get($img_uri, array(
			'timeout'     => self::DOWNLOAD_TIMEOUT,
			'redirection' => 1,
			'sslverify'   => false,
			'user-agent'  => self::getUserAgent($img_uri),
		));

		if (\is_wp_error($response) || (int) \wp_remote_retrieve_response_code($response) !== 200)
			return false;

		if ($file_ext === null)
		{
			$img_path = parse_url($img_uri, PHP_URL_PATH);
			$file_ext = pathinfo(basename($img_path), PATHINFO_EXTENSION);
			if (!$file_ext || $file_ext == 'aspx' || $file_ext == 'image')
			{
				$headers = \wp_remote_retrieve_headers($response);
				if (empty($headers['content-type']))
					return false;

				$types = array_search(str_replace(';charset=UTF-8', '', $headers['content-type']), \wp_get_mime_types());

				if (!$types)
					return false;

				$exts     = explode('|', $types);
				$file_ext = $exts[0];
			}
		}
		if ($file_ext)
			$file_name .= '.' . $file_ext;

		$file_name = \wp_unique_filename($save_dir, $file_name);

		if ($check_image_type)
		{
			$filetype = \wp_check_filetype($file_name, null);
			if (substr($filetype['type'], 0, 5) != 'image')
				return false;
		}

		$image_string = \wp_remote_retrieve_body($response);
		$file_path    = \trailingslashit($save_dir) . $file_name;
		if (!file_put_contents($file_path, $image_string))
			return false;

		if ($check_image_type && !self::isImage($file_path))
		{
			@unlink($file_path);
			return false;
		}

		if (!defined('FS_CHMOD_FILE'))
			define('FS_CHMOD_FILE', (fileperms(ABSPATH . 'index.php') & 0777 | 0644));

		@chmod($file_path, FS_CHMOD_FILE);

		return $file_path;
	}

	public static function isImage($path)
	{
		if (!$a = getimagesize($path))
			return false;

		$image_type = $a[2];

		if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WEBP)))
			return true;
		else
			return false;
	}

	public static function getFullImgPath($img_path)
	{
		$uploads = \wp_upload_dir();
		return trailingslashit($uploads['basedir']) . $img_path;
	}
}
