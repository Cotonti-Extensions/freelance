<?php
/**
 * [BEGIN_COT_EXT]
 * Hooks=projects.userdetails.query
 * [END_COT_EXT]
 */

defined('COT_CODE') or die('Wrong URL.');

require_once cot_incfile('payprjtop', 'plug');

$order = array_merge(['payprjtop' => 'item_top DESC'], $order ?? []);
