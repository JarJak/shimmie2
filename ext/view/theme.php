<?php

class ViewTheme extends Themelet {
	/*
	 * Build a page showing $image and some info about it
	 */
	public function display_page($page, $image, $editor_parts) {
		$page->set_title("Image {$image->id}: ".html_escape($image->get_tag_list()));
		$page->set_heading(html_escape($image->get_tag_list()));
		$page->add_block(new Block("Navigation", $this->build_navigation($image->id), "left", 0));
		$page->add_block(new Block(null, $this->build_info($image, $editor_parts), "main", 10));
		$page->add_block(new Block(null, $this->build_pin($image->id), "main", 11));
	}


	var $pin = null;

	protected function build_pin($image_id) {
		if(!is_null($this->pin)) {
			return $this->pin;
		}

		global $database;

		if(isset($_GET['search'])) {
			$search_terms = explode(' ', $_GET['search']);
			$query = "search=".url_escape($_GET['search']);
		}
		else {
			$search_terms = array();
			$query = null;
		}
		
		$next = $database->get_next_image($image_id, $search_terms);
		$prev = $database->get_prev_image($image_id, $search_terms);

		$h_prev = (!is_null($prev) ? "<a href='".make_link("post/view/{$prev->id}", $query)."'>Prev</a>" : "Prev");
		$h_index = "<a href='".make_link()."'>Index</a>";
		$h_next = (!is_null($next) ? "<a href='".make_link("post/view/{$next->id}", $query)."'>Next</a>" : "Next");

		$this->pin = "$h_prev | $h_index | $h_next";
		return $this->pin;
	}

	protected function build_navigation($image_id) {
		$h_pin = $this->build_pin($image_id);
		$h_search = "
			<p><form action='".make_link()."' method='GET'>
				<input id='search_input' name='search' type='text'
						value='Search' autocomplete='off'>
				<input type='submit' value='Find' style='display: none;'>
			</form>
			<div id='search_completions'></div>";

		return "$h_pin<br>$h_search";
	}

	protected function build_info($image, $editor_parts) {
		global $user;
		$owner = $image->get_owner();
		$h_owner = html_escape($owner->name);
		$h_ip = html_escape($image->owner_ip);
		$h_source = html_escape($image->source);
		$i_owner_id = int_escape($owner->id);

		$html = "";
		$html .= "<p>Uploaded by <a href='".make_link("user/$h_owner")."'>$h_owner</a>";

		if($user->is_admin()) {
			$html .= " ($h_ip)";
		}
		if(!is_null($image->source)) {
			if(substr($image->source, 0, 7) == "http://") {
				$html .= " (<a href='$h_source'>source</a>)";
			}
			else {
				$html .= " (<a href='http://$h_source'>source</a>)";
			}
		}

		$html .= $this->build_image_editor($image, $editor_parts);

		return $html;
	}

	protected function build_image_editor($image, $editor_parts) {
		if(isset($_GET['search'])) {$h_query = "search=".url_escape($_GET['search']);}
		else {$h_query = "";}

		$html = " (<a href=\"javascript: toggle('imgdata')\">edit info</a>)";
		$html .= "
			<div id='imgdata'>
				<form action='".make_link("post/set")."' method='POST'>
					<input type='hidden' name='image_id' value='{$image->id}'>
					<input type='hidden' name='query' value='$h_query'>
					<table style='width: 500px;'>
		";
		foreach($editor_parts as $part) {
			$html .= $part;
		}
		$html .= "
						<tr><td colspan='2'><input type='submit' value='Set'></td></tr>
					</table>
				</form>
				<br>
			</div>
		";
		return $html;
	}
}
?>