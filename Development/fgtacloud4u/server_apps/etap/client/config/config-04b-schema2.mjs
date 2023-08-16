var this_page_id;
var this_page_options;

import {fgta4slideselect2} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect2.mjs'


const pnl_form = $('#pnl_schema2-form')

const box_t1_fieldsset = $('#pnl_schema2-table1-box_fieldsset');
const box_t2_fieldsset = $('#pnl_schema2-table2-box_fieldsset');

const btn_t1_preview = $('#pnl_schema2-table1-btn_preview');
const btn_t2_preview = $('#pnl_schema2-table2-btn_preview');

const btn_next = $('#pnl_schema2-btn_next');


const obj = {
	cbo_t1_tablename: $('#pnl_schema2-table1-cbo_tablename'),
	cbo_t1_field_txid: $('#pnl_schema2-table1-cbo_field_txid'),
	cbo_t1_field_txdate: $('#pnl_schema2-table1-cbo_field_txdate'),
	cbo_t1_field_txdate: $('#pnl_schema2-table1-cbo_field_txdate'),
	cbo_t1_field_txservicecharge: $('#pnl_schema2-cbo_field_txservicecharge'),
	cbo_t1_field_txisvoid: $('#pnl_schema2-table1-cbo_field_txisvoid'),
	chk_t1_schema_isverify : $('#pnl_schema2-table1-chk_schema_isverify'),

	cbo_t2_tablename: $('#pnl_schema2-table2-cbo_tablename'),
	cbo_t2_field_txid: $('#pnl_schema2-table2-cbo_field_txid'),
	cbo_t2_field_itemname: $('#pnl_schema2-table2-cbo_field_itemname'),
	cbo_t2_field_itemqty: $('#pnl_schema2-table2-cbo_field_itemqty'),
	cbo_t2_field_itemsubtotal: $('#pnl_schema2-table2-cbo_field_itemsubtotal'),
	cbo_t2_field_itemtax: $('#pnl_schema2-table2-cbo_field_itemtax'),
	chk_t2_schema_isverify : $('#pnl_schema2-table2-chk_schema_isverify')

}	

let form = {}
var currentconfig = {};
var tablecbo = [];



const tabledata = {
	table1: {
		tableinfoel: $('#pnl_schema2-tableheader'),
		tablecboname: 'cbo_t1_tablename',
		fields: ['cbo_t1_field_txid', 'cbo_t1_field_txdate', 'cbo_t1_field_txservicecharge', 'cbo_t1_field_txisvoid'],
		fieldbox:box_t1_fieldsset
	},
	table2: {
		tableinfoel: $('#pnl_schema2-tabledetil'),
		tablecboname: 'cbo_t2_tablename',
		fields: ['cbo_t2_field_txid', 'cbo_t2_field_itemname', 'cbo_t2_field_itemqty', 'cbo_t2_field_itemsubtotal', 'cbo_t2_field_itemtax'],
		fieldbox: box_t2_fieldsset
	},	
}


export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;



	form = new global.fgta4form(pnl_form, {
		objects : obj,
	});


	$ui.setTableSelector(getCurrentConfig, tablecbo, tabledata, obj, this_page_id, (tableid) => {
		cbo_tablename_selected(tableid)
	});
	$ui.setFieldSelector(getCurrentConfig, tablecbo, tabledata, obj, this_page_id);




	btn_t1_preview.linkbutton({ onClick: () => {  btn_preview_click('table1', obj.chk_t1_schema_isverify.checkbox('options')); } })
	btn_t2_preview.linkbutton({ onClick: () => {  btn_preview_click('table2', obj.chk_t2_schema_isverify.checkbox('options')); } })

	btn_next.linkbutton({ onClick: () => {  btn_next_click(); } });

	obj.chk_t1_schema_isverify.checkbox({onChange: (checked) => { chk_t1_schema_isverify_checked(checked) 	}});


	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show('pnl_selectmodel', (pnl)=>{
				$ui.setPanelShowingInfo(pnl.id);
			})
		};			

	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
		};			
	});


}


export function Reset() {
	btn_next.linkbutton('disable');
	box_t1_fieldsset.hide();
	obj.chk_t1_schema_isverify.checkbox('uncheck');
	ResetDetil();	
	form.setViewMode(false);
}


export function ResetDetil() {
	box_t2_fieldsset.hide();
	var fields = tabledata.table2.fields
	for (var name of fields) {
		obj[name].combo('setValue', '');
		obj[name].combo('setText', '');
	}
	obj.cbo_t2_tablename.combo('disable');
	obj.cbo_t2_tablename.combo('setValue', '');
	obj.cbo_t2_tablename.combo('setText', '');
	obj.chk_t2_schema_isverify.checkbox('uncheck');
}


export async function selecting(currconf) {
	currentconfig = currconf;
}

export async function selected(readfromcurrentconfig) {
	if (readfromcurrentconfig) {
		form.setViewMode(true);

		var t1_tablename = currentconfig.schema.header.tablename;
		var t1_fields = currentconfig.schema.header.fields;
		form.setValue(obj.cbo_t1_tablename, t1_tablename, t1_tablename);

		if (t1_tablename!='') {
			box_t1_fieldsset.show();
			form.setValue(obj.cbo_t1_field_txid, t1_fields.txid, t1_fields.txid);
			form.setValue(obj.cbo_t1_field_txdate, t1_fields.txdate, t1_fields.txdate);
			form.setValue(obj.cbo_t1_field_txservicecharge, t1_fields.txservicecharge, t1_fields.txservicecharge);
			form.setValue(obj.cbo_t1_field_txisvoid, t1_fields.txisvoid, t1_fields.txisvoid);
		}

		box_t2_fieldsset.hide();
		var t2_tablename = currentconfig.schema.items.tablename;
		var t2_fields = currentconfig.schema.items.fields;
		form.setValue(obj.cbo_t2_tablename, t2_tablename, t2_tablename);
		obj.cbo_t2_tablename.combo('disable');
		if (t2_tablename!='') {
			form.setValue(obj.cbo_t2_field_txid, t2_fields.txid, t2_fields.txid);
			form.setValue(obj.cbo_t2_field_itemname, t2_fields.itemname, t2_fields.itemname);
			form.setValue(obj.cbo_t2_field_itemqty, t2_fields.itemqty, t2_fields.itemqty);
			form.setValue(obj.cbo_t2_field_itemsubtotal, t2_fields.itemsubtotal, t2_fields.itemsubtotal);
			form.setValue(obj.cbo_t2_field_itemtax, t2_fields.itemtax, t2_fields.itemtax);
		}
	} else {

		box_t1_fieldsset.hide();
		obj.cbo_t1_tablename.combo('enable');
		form.setValue(obj.cbo_t1_tablename, '', '--PILIH--');
		form.setValue(obj.cbo_t1_field_txid, '', '--PILIH--');
		form.setValue(obj.cbo_t1_field_txdate, '', '--PILIH--');
		form.setValue(obj.cbo_t1_field_txservicecharge, '', '--PILIH--');
		form.setValue(obj.cbo_t1_field_txisvoid, '', '--PILIH--');

		box_t2_fieldsset.hide();
		obj.cbo_t2_tablename.combo('disable');
		form.setValue(obj.cbo_t2_tablename, '', '--PILIH--');
		form.setValue(obj.cbo_t2_field_txid, '', '--PILIH--');
		form.setValue(obj.cbo_t2_field_itemname, '', '--PILIH--');
		form.setValue(obj.cbo_t2_field_itemqty, '', '--PILIH--');
		form.setValue(obj.cbo_t2_field_itemsubtotal, '', '--PILIH--');
		form.setValue(obj.cbo_t2_field_itemtax, '', '--PILIH--');
	}

	obj.chk_t1_schema_isverify.checkbox('uncheck')
	obj.chk_t2_schema_isverify.checkbox('uncheck')
	form.setViewMode(false);
}

export async function verified(varifytable, checked) {
	
	form.setViewMode(true);
	switch (varifytable) {
		case "table1" :
			obj.chk_t1_schema_isverify.checkbox(checked? 'check' : 'uncheck');
			if (checked) {
				obj.cbo_t2_tablename.combo('enable');
				obj.cbo_t2_field_txid.combo('enable');
				obj.cbo_t2_field_itemname.combo('enable');
				obj.cbo_t2_field_itemqty.combo('enable');
				obj.cbo_t2_field_itemsubtotal.combo('enable');
				obj.cbo_t2_field_itemtax.combo('enable');
				if (obj.cbo_t2_tablename.combo('getValue')!='') {
					box_t2_fieldsset.show();
				}
				table1_lock(true);
			} else {
				ResetDetil();
				table1_lock(false);
			}
			await save_configuration_header(checked);
			break;
		case "table2" :
			obj.chk_t2_schema_isverify.checkbox(checked? 'check' : 'uncheck');
			table2_lock(checked ? true : false);
			await save_configuration_items(checked);
			break;
				
	}
	form.setViewMode(false);

	var chk1 = obj.chk_t1_schema_isverify.checkbox('options');
	var chk2 = obj.chk_t2_schema_isverify.checkbox('options');

	if (chk1.checked && chk2.checked) {
		btn_next.linkbutton('enable');
	} else {
		btn_next.linkbutton('disable');
	}
}

function getCurrentConfig() {
	return currentconfig;
}

function btn_preview_click(tableid, chk) {

	var tableinfo = {};
	switch (tableid) {
		case 'table1' :
			tableinfo = {
				model: 'model-a',
				tablename: obj.cbo_t1_tablename.combo('getValue'),
				fields: {
					txid : obj.cbo_t1_field_txid.combo('getValue'),
					txdate: obj.cbo_t1_field_txdate.combo('getValue'),
					txservicecharge: obj.cbo_t1_field_txservicecharge.combo('getValue'),
					txisvoid: obj.cbo_t1_field_txisvoid.combo('getValue'),
				}
			}	
			break;

		case 'table2' :
			tableinfo = {
				model: 'model-b',
				tablename: obj.cbo_t2_tablename.combo('getValue'),
				fields: {
					txid : obj.cbo_t2_field_txid.combo('getValue'),
					itemname: obj.cbo_t2_field_itemname.combo('getValue'),
					itemqty: obj.cbo_t2_field_itemqty.combo('getValue'),
					itemsubtotal: obj.cbo_t2_field_itemsubtotal.combo('getValue'),
					itemtax: obj.cbo_t2_field_itemtax.combo('getValue'),					
				}
			}
			break;
	}



	var pnl_verify = $ui.getPages().ITEMS['pnl_verify'];
	pnl_verify.handler.setVerifierFor({
		currconf: currentconfig,
		panelname: 'pnl_schema2', 
		tableid: tableid, 
		verifynote:  tabledata[tableid].tableinfoel.attr('verifynote'),
		verifytitle: tabledata[tableid].tableinfoel.attr('title'),
		tableinfo: tableinfo	
	});

	$ui.getPages().show(pnl_verify.id, () => {
		$ui.setPanelShowingInfo(pnl_verify.id);
		pnl_verify.handler.Reset({
			already_verified : chk.checked
		});
	});
}



function cbo_tablename_selected(tableid) {
}


function chk_t1_schema_isverify_checked(checked) {
}


async function save_configuration_header(checked) {
	currentconfig.schema.header.tablename = obj.cbo_t1_tablename.combo('getValue');
	currentconfig.schema.header.isverified = checked;
	Object.assign(currentconfig.schema.header.fields, {
		txid: obj.cbo_t1_field_txid.combo('getValue'),
		txdate: obj.cbo_t1_field_txdate.combo('getValue'),
		txservicecharge: obj.cbo_t1_field_txservicecharge.combo('getValue'),
		txisvoid:  obj.cbo_t1_field_txisvoid.combo('getValue'),
		itemname: '',       // not relevant
		itemqty: '',        // not relevant
		itemsubtotal: '',   // not relevant
		itemtax: ''         // not relevant
	});

	try {
		await global.cachedb.setup_update_setting(currentconfig);
	} catch (err) {
		$ui.ShowMessage("[ERROR]Cannot Update Configuration");
	}
}

async function save_configuration_items(checked) {
	currentconfig.schema.items.tablename = obj.cbo_t2_tablename.combo('getValue');
	currentconfig.schema.items.isverified = checked;
	Object.assign(currentconfig.schema.items.fields, {
		txid: obj.cbo_t2_field_txid.combo('getValue'),
		itemname: obj.cbo_t2_field_itemname.combo('getValue'),
		itemqty: obj.cbo_t2_field_itemqty.combo('getValue'),
		itemsubtotal: obj.cbo_t2_field_itemsubtotal.combo('getValue'),
		itemtax: obj.cbo_t2_field_itemtax.combo('getValue')
	});

	try {
		await global.cachedb.setup_update_setting(currentconfig);
	} catch (err) {
		$ui.ShowMessage("[ERROR]Cannot Update Configuration");
	}
}



function table1_lock(lock) {
	obj.cbo_t1_tablename.combo(lock ? 'disable' : 'enable');
	obj.cbo_t1_field_txid.combo(lock ? 'disable' : 'enable');
	obj.cbo_t1_field_txdate.combo(lock ? 'disable' : 'enable');
	obj.cbo_t1_field_txservicecharge.combo(lock ? 'disable' : 'enable');
	obj.cbo_t1_field_txisvoid.combo(lock ? 'disable' : 'enable');
}


function table2_lock(lock) {
	obj.cbo_t2_tablename.combo(lock ? 'disable' : 'enable');
	obj.cbo_t2_field_txid.combo(lock ? 'disable' : 'enable');
	obj.cbo_t2_field_itemname.combo(lock ? 'disable' : 'enable');
	obj.cbo_t2_field_itemqty.combo(lock ? 'disable' : 'enable');
	obj.cbo_t2_field_itemsubtotal.combo(lock ? 'disable' : 'enable');
	obj.cbo_t2_field_itemtax.combo(lock ? 'disable' : 'enable');
}


function btn_next_click() {
	var pnl_final = $ui.getPages().ITEMS['pnl_final'];
	pnl_final.handler.setFinalizedFor({
		currconf: currentconfig,
		panelname: 'pnl_schema2', 
	});
	$ui.getPages().show(pnl_final, (pnl) => {
		$ui.setPanelShowingInfo(pnl.id);
	});
}