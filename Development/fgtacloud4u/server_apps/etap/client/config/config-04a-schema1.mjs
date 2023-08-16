var this_page_id;
var this_page_options;

import {fgta4slideselect2} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect2.mjs'


const pnl_form = $('#pnl_schema1-form')
const box_fieldsset = $('#pnl_schema1-box_fieldsset');
const btn_preview = $('#pnl_schema1-btn_preview');
const btn_next = $('#pnl_schema1-btn_next');


const obj = {
	cbo_tablename: $('#pnl_schema1-cbo_tablename'),
	cbo_field_txid: $('#pnl_schema1-cbo_field_txid'),
	cbo_field_txdate: $('#pnl_schema1-cbo_field_txdate'),
	cbo_field_txservicecharge: $('#pnl_schema1-cbo_field_txservicecharge'),
	cbo_field_txisvoid: $('#pnl_schema1-cbo_field_txisvoid'),
	cbo_field_itemname: $('#pnl_schema1-cbo_field_itemname'),
	cbo_field_itemqty: $('#pnl_schema1-cbo_field_itemqty'),
	cbo_field_itemsubtotal: $('#pnl_schema1-cbo_field_itemsubtotal'),
	cbo_field_itemtax: $('#pnl_schema1-cbo_field_itemtax'),
	chk_schema_isverify : $('#pnl_schema1-chk_schema_isverify')
}	

let form = {}
var currentconfig = {};
var tablecbo = [];


const tabledata = {
	table1: {
		tableinfoel: $('#pnl_schema1-tabledata'),
		tablecboname: 'cbo_tablename',
		fields: ['cbo_field_txid', 'cbo_field_txdate', 'cbo_field_txservicecharge', 'cbo_field_txisvoid', 'cbo_field_itemname', 'cbo_field_itemqty', 'cbo_field_itemsubtotal', 'cbo_field_itemtax'], 
		fieldbox:box_fieldsset
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

	btn_preview.linkbutton({ onClick: () => {  btn_preview_click('table1', obj.chk_schema_isverify.checkbox('options')); } })
	btn_next.linkbutton({ onClick: () => {  btn_next_click(); } });



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
	box_fieldsset.hide();
	for (var name in obj) {
		var iscombo = obj[name].hasClass('easyui-combo') ? true : false;
		if (!iscombo) continue;
		obj[name].combo('setValue', '');
		obj[name].combo('setText', '');
	}
}


export async function selecting(currconf) {
	currentconfig = currconf;
}

export async function selected(readfromcurrentconfig) {
	form.setViewMode(true);
	if (readfromcurrentconfig) {
		var t1_tablename = currentconfig.schema.header.tablename;
		var t1_fields = currentconfig.schema.header.fields;
		// console.log(t1_fields);
		form.setValue(obj.cbo_tablename, t1_tablename, t1_tablename);
		if (t1_tablename!='') {
			box_fieldsset.show();
			form.setValue(obj.cbo_field_txid, t1_fields.txid, t1_fields.txid);
			form.setValue(obj.cbo_field_txdate, t1_fields.txdate, t1_fields.txdate);
			form.setValue(obj.cbo_field_txservicecharge, t1_fields.txservicecharge, t1_fields.txservicecharge);
			form.setValue(obj.cbo_field_txisvoid, t1_fields.txisvoid, t1_fields.txisvoid),
			form.setValue(obj.cbo_field_itemname, t1_fields.itemname, t1_fields.itemname),
			form.setValue(obj.cbo_field_itemqty, t1_fields.itemqty, t1_fields.itemqty),
			form.setValue(obj.cbo_field_itemsubtotal, t1_fields.itemsubtotal, t1_fields.itemsubtotal),
			form.setValue(obj.cbo_field_itemtax, t1_fields.itemtax, t1_fields.itemtax)
		}
	} else {
		box_fieldsset.hide();
		obj.cbo_tablename.combo('enable');
		form.setValue(obj.cbo_tablename, '', '--PILIH--');
		form.setValue(obj.cbo_field_txid, '', '--PILIH--');
		form.setValue(obj.cbo_field_txdate, '', '--PILIH--');
		form.setValue(obj.cbo_field_txservicecharge, '', '--PILIH--');
		form.setValue(obj.cbo_field_txisvoid, '', '--PILIH--'),
		form.setValue(obj.cbo_field_itemname, '', '--PILIH--'),
		form.setValue(obj.cbo_field_itemqty, '', '--PILIH--'),
		form.setValue(obj.cbo_field_itemsubtotal, '', '--PILIH--'),
		form.setValue(obj.cbo_field_itemtax, '', '--PILIH--')	
	}

	obj.chk_schema_isverify.checkbox('uncheck')
	form.setViewMode(false);
}

export async function verified(varifytable, checked) {
	form.setViewMode(true);
	switch (varifytable) {
		case "table1" :
			obj.chk_schema_isverify.checkbox(checked? 'check' : 'uncheck');
			table1_lock(checked ? true : false);
			await save_configuration_header(checked)
	}
	form.setViewMode(false);
	

	var chk1 = obj.chk_schema_isverify.checkbox('options');
	if (chk1.checked ) {
		btn_next.linkbutton('enable');
	} else {
		btn_next.linkbutton('disable');
	}	
}


function getCurrentConfig() {
	return currentconfig;
}

function btn_preview_click(tableid, chk) {
	var pnl_verify = $ui.getPages().ITEMS['pnl_verify'];
	pnl_verify.handler.setVerifierFor({
		currconf: currentconfig,
		panelname: 'pnl_schema1', 
		tableid: tableid, 
		verifynote:  tabledata[tableid].tableinfoel.attr('verifynote'),
		verifytitle: tabledata[tableid].tableinfoel.attr('title'),
		tableinfo: {
			model: 'model-d',
			tablename: obj.cbo_tablename.combo('getValue'),
			fields: {
				txid : obj.cbo_field_txid.combo('getValue'),
				txdate: obj.cbo_field_txdate.combo('getValue'),
				txservicecharge: obj.cbo_field_txservicecharge.combo('getValue'),
				txisvoid: obj.cbo_field_txisvoid.combo('getValue'),
				itemname: obj.cbo_field_itemname.combo('getValue'),
				itemqty: obj.cbo_field_itemqty.combo('getValue'),
				itemsubtotal: obj.cbo_field_itemsubtotal.combo('getValue'),
				itemtax: obj.cbo_field_itemtax.combo('getValue'),
			}
		}
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


async function save_configuration_header(checked) {
	currentconfig.schema.header.tablename = obj.cbo_tablename.combo('getValue');
	currentconfig.schema.header.isverified = checked;
	Object.assign(currentconfig.schema.header.fields, {
		txid: obj.cbo_field_txid.combo('getValue'),
		txdate: obj.cbo_field_txdate.combo('getValue'),
		txservicecharge: obj.cbo_field_txservicecharge.combo('getValue'),
		txisvoid:  obj.cbo_field_txisvoid.combo('getValue'),
		itemname: obj.cbo_field_itemname.combo('getValue'),
		itemqty: obj.cbo_field_itemqty.combo('getValue'),
		itemsubtotal: obj.cbo_field_itemsubtotal.combo('getValue'),
		itemtax: obj.cbo_field_itemtax.combo('getValue'),
	});

	try {
		console.log(currentconfig.schema.header.fields)
		await global.cachedb.setup_update_setting(currentconfig);
	} catch (err) {
		$ui.ShowMessage("[ERROR]Cannot Update Configuration");
	}
}




function table1_lock(lock) {
	obj.cbo_tablename.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_txid.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_txdate.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_txservicecharge.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_txisvoid.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_itemname.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_itemqty.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_itemsubtotal.combo(lock ? 'disable' : 'enable');
	obj.cbo_field_itemtax.combo(lock ? 'disable' : 'enable');
}


function btn_next_click() {
	var pnl_final = $ui.getPages().ITEMS['pnl_final'];
	pnl_final.handler.setFinalizedFor({
		currconf: currentconfig,
		panelname: 'pnl_schema1', 
	});

	$ui.getPages().show(pnl_final, (pnl) => {
		$ui.setPanelShowingInfo(pnl.id);
	});
}