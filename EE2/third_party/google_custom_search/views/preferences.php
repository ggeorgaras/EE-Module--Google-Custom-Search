<?php
// 
//  preferences.php
//  AutoMin Module
//  
//  Created by Jesse Bunch on 2011-04-16.
//  Copyright 2011 Jesse Bunch (www.GetBunch.com). All rights reserved.
// 

/*

	This template allows the user to edit AutoMin preferences

	Template Variables:
	$arrCalloutFieldGroups - An array containing all the callout field groups

*/


// -------------------------------------
// 	Set template
// -------------------------------------

$this->table->set_template($cp_table_template);
$this->table->set_heading(
						lang('setting'), 
						array('data' => lang('value'), 'style' => 'width:65%;')
);

// -------------------------------------
// 	Add Form Rows
// -------------------------------------

$arrYesNo = array('y' => lang('yes'), 'n' => lang('no'));

$this->table->add_row(
	form_label(lang('api_key'), 'api_key') . '<br>' . lang('api_key_instructions'),
	form_input('data[api_key]', (isset($arrSettings['api_key'])) ? $arrSettings['api_key'] : '')
);

$this->table->add_row(
	form_label(lang('custom_search_id'), 'custom_search_id') . '<br>' . lang('custom_search_id_instructions'),
	form_input('data[custom_search_id]', (isset($arrSettings['custom_search_id'])) ? $arrSettings['custom_search_id'] : '')
);

// $this->table->add_row(
// 	form_label(lang('cache_enabled'), 'cache_enabled'),
// 	form_dropdown('data[cache_enabled]', $arrYesNo, (isset($arrSettings['cache_enabled'])) ? $arrSettings['cache_enabled'] : '')
// );
// 
// $this->table->add_row(
// 	form_label(lang('cache_time'), 'cache_time') . '<br>' . lang('cache_time_instructions'),
// 	form_input('data[cache_time]', (isset($arrSettings['cache_time'])) ? $arrSettings['cache_time'] : '')
// );
	
// -------------------------------------
// 	Write out the view
// -------------------------------------

?>
<?=form_open($_strFormBase.AMP.'method=SetPreferences_Submit');?>
<?=(isset($arrHiddenVars)) ? form_hidden($arrHiddenVars) : '';?>
<?=$this->table->generate();?>
<?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));?>
<?=form_close();?>