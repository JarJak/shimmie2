<?php

class UploadTheme extends Themelet {
	public function display_block(Page $page) {
		$page->add_block(new Block("Upload", $this->build_upload_block(), "left", 20));
	}

	public function display_full(Page $page) {
		$page->add_block(new Block("Upload", "Disk nearly full, uploads disabled", "left", 20));
	}

	public function display_page(Page $page) {
		global $config;
		$tl_enabled = ($config->get_string("transload_engine", "none") != "none");
		
		// Uploader 2.0!
		$upload_list = "";
		for($i=0; $i<$config->get_int('upload_count'); $i++) {
			$n = $i + 1;
			$width = $tl_enabled ? "35%" : "80%";
			$upload_list .= "
				<tr>
					<td width='60'><form><input id='radio_buttona' type='radio' name='method' value='file' checked='checked' onclick='javascript:document.getElementById(&quot;url$i&quot;).style.display = &quot;none&quot;;document.getElementById(&quot;url$i&quot;).value = &quot;&quot;;document.getElementById(&quot;data$i&quot;).style.display = &quot;inline&quot;' /> File<br>";
				if($tl_enabled) {
					$upload_list .="
					<input id='radio_buttonb' type='radio' name='method' value='url' onclick='javascript:document.getElementById(&quot;data$i&quot;).style.display = &quot;none&quot;;document.getElementById(&quot;data$i&quot;).value = &quot;&quot;;document.getElementById(&quot;url$i&quot;).style.display = &quot;inline&quot;' /> URL</ br></td></form>
					
					<td><input id='data$i' name='data$i' class='wid' type='file'><input id='url$i' name='url$i' class='wid' type='text' style='display:none'></td>
					";
					}
					else { 
					$upload_list .= "</form></td>
					<td width='250'><input id='data$i' name='data$i' class='wid' type='file'></td>
					";
					}
					
			$upload_list .= "
				</tr>
			";
		}
		$max_size = $config->get_int('upload_size');
		$max_kb = to_shorthand_int($max_size);
		$html = "
			<script type='text/javascript'>
			$(document).ready(function() {
				$('#tag_box').DefaultValue('tagme');
				$('#tag_box').autocomplete('".make_link("api/internal/tag_list/complete")."', {
					width: 320,
					max: 15,
					highlight: false,
					multiple: true,
					multipleSeparator: ' ',
					scroll: true,
					scrollHeight: 300,
					selectFirst: false
				});
			});
			</script>
			".make_form(make_link("upload"), "POST", $multipart=True)."
				<table id='large_upload_form' class='vert'>
					$upload_list
					<tr><td>Tags</td><td colspan='3'><input id='tag_box' name='tags' type='text'></td></tr>
					<tr><td>Source</td><td colspan='3'><input name='source' type='text'></td></tr>
					<tr><td colspan='4'><input id='uploadbutton' type='submit' value='Post'></td></tr>
				</table>
			</form>
			<small>(Max file size is $max_kb)</small>
		";
		
		if($tl_enabled) {
			$link = make_http(make_link("upload"));			
			if($config->get_bool('nice_urls')){
				$delimiter = '?';
			} else {
				$delimiter = '&amp;';
			}
				{
			$title = "Upload to " . $config->get_string('title');
			$html .= '<p><a href="javascript:location.href=&quot;' .
				$link . $delimiter . 'url=&quot;+location.href+&quot;&amp;tags=&quot;+prompt(&quot;enter tags&quot;)">' .
				$title . '</a> (Drag & drop onto your bookmarks toolbar, then click when looking at an image)';
			}
				{
			$title = "Danbooru to " . $config->get_string('title');
			$html .= '<p><a href="javascript:var img=document.getElementById(&quot;highres&quot;).href;var ste=&quot;' .
				$link . $delimiter . 'url=&quot;;var tag=document.getElementById(&quot;post_old_tags&quot;).value;if (confirm(&quot;OK = Use Current tags.\nCancel = Use new tags.&quot;)==true)' .
				'{if(tag.search(/\bflash\b/)==-1){location.href=ste+img+&quot;&amp;tags=&quot;+tag;}else{location.href=ste+document.getElementsByName(&quot;movie&quot;)[0].value' .
				'+&quot;&amp;tags=&quot;+tag;}}else{var p=prompt(&quot;Enter Tags&quot;,&quot;&quot;);if(tag.search(/\bflash\b/)==-1){location.href=ste+img+&quot;&amp;tags=&quot;+p;}' .
				'else{location.href=ste+document.getElementsByName(&quot;movie&quot;)[0].value+&quot;&amp;tags=&quot;+p;}}">' .
				$title . '</a> (As above, Click on a Danbooru-run image page. (This also grabs the tags!))';

			}
				
		}

		$page->set_title("Upload");
		$page->set_heading("Upload");
		$page->add_block(new NavBlock());
		$page->add_block(new Block("Upload", $html, "main", 20));
	}

	/* only allows 1 file to be uploaded - for replacing another image file */
	public function display_replace_page(Page $page, $image_id) {
		global $config;
		$tl_enabled = ($config->get_string("transload_engine", "none") != "none");

		$upload_list = '';
		$width = $tl_enabled ? "35%" : "80%";
		$upload_list .= "
			<tr>
				<td width='50'>File</td>
				<td width='250'><input id='data0' name='data0' type='file'></td>
			</tr>
		";
		if($tl_enabled) {
			$upload_list .= "
			<tr>
				<td width='50'>URL</td>
				<td width='250'><input id='url0' name='url0' type='text'></td>
			</tr>
			";
		}

		$max_size = $config->get_int('upload_size');
		$max_kb = to_shorthand_int($max_size);
		
		$image = Image::by_id($image_id);
		$thumbnail = $this->build_thumb_html($image, null);
		
		$html = "
				<div style='clear:both;'></div>
				<p>Replacing Image ID ".$image_id."<br>Please note: You will have to refresh the image page, or empty your browser cache.</p>"
				.$thumbnail."<br>"
				.make_form(make_link("upload/replace/".$image_id), "POST", $multipart=True)."
				<input type='hidden' name='image_id' value='$image_id'>
				<table id='large_upload_form'>
					$upload_list
					<tr><td>Source</td><td colspan='3'><input name='source' type='text'></td></tr>
					<tr><td colspan='4'><input id='uploadbutton' type='submit' value='Post'></td></tr>
				</table>
			</form>
			<small>(Max file size is $max_kb)</small>
		";

		$page->set_title("Replace Image");
		$page->set_heading("Replace Image");
		$page->add_block(new NavBlock());
		$page->add_block(new Block("Upload Replacement Image", $html, "main", 20));
	}
	
	public function display_upload_status(Page $page, $ok) {
		if($ok) {
			$page->set_mode("redirect");
			$page->set_redirect(make_link());
		}
		else {
			$page->set_title("Upload Status");
			$page->set_heading("Upload Status");
			$page->add_block(new NavBlock());
		}
	}

	public function display_upload_error(Page $page, $title, $message) {
		$page->add_block(new Block($title, $message));
	}

	protected function build_upload_block() {
		global $config;

		$upload_list = "";
		for($i=0; $i<$config->get_int('upload_count'); $i++) {
			if($i == 0) $style = ""; // "style='display:visible'";
			else $style = "style='display:none'";
			$upload_list .= "<input size='10' ".
				"id='data$i' name='data$i' $style onchange=\"$('#data".($i+1)."').show()\" type='file'>\n";
		}
		$max_size = $config->get_int('upload_size');
		$max_kb = to_shorthand_int($max_size);
		// <input type='hidden' name='max_file_size' value='$max_size' />
		return "
			<script type='text/javascript'>
			$(document).ready(function() {
				$('#tag_input').DefaultValue('tagme');
				$('#tag_input').autocomplete('".make_link("api/internal/tag_list/complete")."', {
					width: 320,
					max: 15,
					highlight: false,
					multiple: true,
					multipleSeparator: ' ',
					scroll: true,
					scrollHeight: 300,
					selectFirst: false
				});
			});
			</script>
			".make_form(make_link("upload"), "POST", $multipart=True)."
				$upload_list
				<input id='tag_input' name='tags' type='text' autocomplete='off'>
				<input type='submit' value='Post'>
			</form>
			<div id='upload_completions' style='clear: both;'><small>(Max file size is $max_kb)</small></div>
			<noscript><a href='".make_link("upload")."'>Larger Form</a></noscript>
		";
	}
}
?>