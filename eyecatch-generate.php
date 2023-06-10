<?php
/**
 * Generate eyecatch
 *
 * @package WordPress
 */

// phpcs:ignore
if ( isset( $_POST['postId'] ) ) {
	$post_id = $_POST['postId'];
}

try {
	if ( $post_id && get_post( $post_id ) ){
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		// プラグインフォルダから背景とフォントを読み込みます
		$base_img = new Imagick( $plugin_dir_path . 'img/bg_thumb.png' );
		$draw =new ImagickDraw();
		$draw->setFont( $plugin_dir_path . 'fonts/NotoSansJP-VariableFont_wght.ttf');
		$draw->setFontSize( 40 );
	
		// imagickのカラー設定
		$pixel = new ImagickPixel();
		$pixel->setColor( "#000000" );
		$draw->setFillColor( $pixel );
	
		// 投稿IDからタイトルを取得
		$title = get_post( $post_id )->post_title;
		$outtxt = '';
	
		// 16文字以上なら改行をいれる
		// 改行をいれる箇所を正規表現でパターン分け
		if ( mb_strlen( $title ) > 16 ) {
			$strlen = mb_strlen( $title );
	
			$minus3 = intval( $strlen / 2 ) - 3;
			$plus3 = intval( $strlen / 2 ) + 3;
			$minus4 = intval( $strlen / 2 ) - 4;
			$plus4 = intval( $strlen / 2 ) + 4;
			$minus5 = intval( $strlen / 2 ) - 5;
			$plus5 = intval( $strlen / 2 ) + 5;
			$minus8 = intval( $strlen / 2 ) - 8;
			$plus8 = intval( $strlen / 2 ) + 8;
	
			if (
				// 真ん中前後の接続詞後改行
				preg_match( '/^(.{' . $minus3 . ',' . $plus3 . '}[を|に|の|で|から|へ])(.+)$/u', $title, $m )
				// 真ん中前後の句読点削除後改行
				|| preg_match( '/^(.{' . $minus3 . ',' . $plus3 . '})[,|.|:|、|。](.+)$/u', $title, $m )
				// 真ん中前後の記号前改行
				|| preg_match( '/^(.{' . $minus3 . ',' . $plus3 . '})([\(|（|【|「|\-|～].+)$/u', $title, $m )
				// 真ん中前後の記号後改行
				|| preg_match('/^(.{' . $minus3 . ',' . $plus3 . '}[\)|）|】|」|\-|～][^\n]+)(.+)$/u', $title, $m )
				// 真ん中前後のローマ字とそれ以外の区切りで改行	
				|| preg_match( '/^(.{' . $minus4 . ',' . $plus4 . '}[a-zA-Z0-9]+)([^a-zA-Z0-9]+.+)$/u', $title, $m )
				|| preg_match( '/^(.{' . $minus4 . ',' . $plus4 . '}[^a-zA-Z0-9]+)([a-zA-Z0-9]+.+)$/u', $title, $m )
				//上記の範囲を緩和
				|| preg_match('/^(.{' . $minus5 . ',' . $plus5 . '}[を|に|の|で|から|へ])(.*)$/u', $title, $m )
				|| preg_match('/^(.{' . $minus5 . ',' . $plus5 . '})[,|.|:|、|。](.*)$/u', $title, $m )
				|| preg_match('/^(.{' . $minus5 . ',' . $plus5 . '})([\(|（|【|「|\-|～][^\n]+)$/u', $title, $m )
				|| preg_match('/^(.{' . $minus5 . ',' . $plus5 . '}[\)|）|】|」|\-|～][^\n]+)(.*)$/u', $title, $m )
				|| preg_match('/^(.{' . $minus8 . ',' . $plus8 . '}[a-zA-Z0-9]+)([^a-zA-Z0-9]+.*)$/u', $title, $m )
				|| preg_match('/^(.{' . $minus8 . ',' . $plus8 . '}[^a-zA-Z0-9]+)([a-zA-Z0-9]+.*)$/u', $title, $m )
			) {
				$outtxt = $m[1] . "\n" . $m[2];
			} else {
				//半分で強制にわける
				$outtxt= mb_substr( $title, 0, intval( $strlen / 2 ) ) . "\n" . mb_substr( $title, intval( $strlen / 2 ) );
			}
		} else {
			$outtxt = $title;
		}
	
		// テキストの情報を取得するためのもの
		$metrics = $base_img->queryFontMetrics( $draw, $outtxt );
		// 中央起点にする
		$draw->setTextAlignment( imagick::ALIGN_CENTER );
		// テキストを画像に貼り付けるフォントによって微調整が必要な場合も
		$draw->annotation(
			$base_img->getImageWidth() / 2,
			( $base_img->getImageHeight() - $metrics['textHeight'] ) / 2 + $metrics['ascender'],
			$outtxt
		);
		$base_img->drawImage( $draw );
		// ここからはWordPressのフォルダを取得～保存名、保存先
		$wp_upload_dir = wp_upload_dir();
		$thumb_name = 'thumb_' . $post_id . '.png';
		$thumb_path = $wp_upload_dir['path'] . '/' . $thumb_name;
		$thumb_url = site_url() . '/wp-content/uploads/' . $thumb_name;
		
		if (
			$attach_id = attachment_url_to_postid( $thumb_url )
		) {
			//すでにメディアに登録されている場合、画像だけ更新してアイキャッチに登録して終わる
			$base_img->writeImage( $thumb_path );
			$base_img->destroy();
			set_post_thumbnail( $post_id, $attach_id );
		} else {
			$base_img->writeImage($thumb_path);
			$base_img->destroy();
	
			//メディアのライブラリに登録する処理
			$wp_filetype = wp_check_filetype( $thumb_path, null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => sanitize_file_name( $thumb_name ),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $thumb_path );
			echo $attach_id;
			
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $thumb_path );
			$rtn = wp_update_attachment_metadata( $attach_id,  $attach_data );
	
			//アイキャッチに登録
			set_post_thumbnail( $post_id, $attach_id );
		}
	
		echo 'OK';
		die;
	}
} catch (\Throwable $th) {
	echo "NG";
}

