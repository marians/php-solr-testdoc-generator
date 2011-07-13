<?php

/**
 * Creates test documents for Solr
 *
 * @author	Marian Steinbach
 * @license	Public Domain
 */

// Simple word list, as in
// http://www.karamasoft.com/UltimateSpell/Dictionary/English%20(United%20States)/en-US.zip
$dictfile = 'dictionary/en-US/en-US.dic';

$num_docs = 1000;

$output_folder = 'testdocs';

function load_dictionary($filepath) {
	$words = file($filepath);
	$dict = array(
		'nouns' => array(),
		'nonnouns' => array()
	);
	foreach ($words AS $word) {
		$first = substr($word, 0, 1);
		$word = trim($word);
		$parts = explode("'", $word);
		$dict[] = $parts[0];
	}
	return $dict;
}

function get_random_sentence($maxlength, $period=false) {
	global $dict;
	$words = array();
	$num = 0;
	$shortwords = array('in', 'to', 'by', 'as', 'for', 'at', 'from', 'is', 'are', 'where', 'of');
	$length = rand(intval($maxlength * 0.3), $maxlength);
	$randkeys = array_rand($dict, $length);
	while ($num < $length) {
		$theword = '';
		if ($num % 3 == 1) {
			$theword = $shortwords[array_rand($shortwords, 1)];
		} else {
			$theword = $dict[$randkeys[$num]];
		}
		if ($num == 0) {
			if (is_array($theword)) {
				echo "WARNING: Array detected instead of string.\n";
				echo var_export($theword, true)."\n";
			}
			$theword = ucfirst($theword);
		}
		$words[] = $theword;
		$num++;
	}
	$sentence = implode(' ', $words);
	if ($period) {
		$sentence .= '.';
	}
	return $sentence;
}

function get_random_sentences($num, $maxlength) {
	global $dict;
	$sentences = array();
	$length = max(1, rand(intval($num * 0.5), $num));
	for ($i=0; $i<$length; $i++) {
		$sentences[] = get_random_sentence($maxlength, true);
	}
	return implode(' ', $sentences);
}

function get_random_paragraph($maxnumsentences, $maxsentencelength) {
	return get_random_sentences($maxnumsentences, $maxsentencelength)."\n\n";
}

function get_random_paragraphs($num, $maxnumsentences, $maxsentencelength) {
	global $dict;
	$num_paragraphs = max(1, rand(intval($num * 0.3), $num));
	$paragraphs = array();
	for ($i=0; $i<$num_paragraphs; $i++) {
		$paragraphs[] = get_random_sentences($maxnumsentences, $maxsentencelength);
	}
	return implode("\n\n", $paragraphs);
}

function get_random_document() {
	global $dict;
	$doc = array('title', 'description', 'body');
	$doc['title'] = get_random_sentence(20);
	$doc['description'] = get_random_sentences(3, 30);
	$doc['body'] = get_random_paragraphs(20, 10, 40);
	return $doc;
}

function print_doc_as_xml($doc, $path) {
	$id = md5(strval(microtime(true)));
	$str = '<?xml version="1.0" encoding="utf-8" ?>'."\n";
	$str .= "<add>\n";
	$str .= "	<doc>\n";
	$str .= "		<field name='id'>testdoc-".$id."</field>\n";
	$str .= "		<field name='categoryid'>testdoc</field>\n";
	$str .= "		<field name='categoryname'>Test Documents</field>\n";
	$str .= "		<field name='url'>http://example.com/testdocs/".$id."</field>\n";
	$str .= "		<field name='title'>".$doc['title']."</field>\n";
	$str .= "		<field name='description'>".$doc['description']."</field>\n";
	$str .= "		<field name='body'>".$doc['body']."</field>\n";
	$str .= "	</doc>\n";
	$str .= "</add>\n";
	$fp = fopen($path, 'w');
	fwrite($fp, $str);
	fclose($fp);
}

$dict = load_dictionary($dictfile);
for ($i=0; $i<$num_docs; $i++) {
	$doc = get_random_document();
	print_doc_as_xml($doc, $output_folder.'/'.$i.'.xml');
}

?>
