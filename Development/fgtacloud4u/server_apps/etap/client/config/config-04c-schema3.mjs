var this_page_id;
var this_page_options;

import {fgta4slideselect2} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect2.mjs'


const pnl_form = $('#pnl_schema3-form')

const box_t1_fieldsset = $('#pnl_schema3-table1-box_fieldsset');
const box_t2_fieldsset = $('#pnl_schema3-table2-box_fieldsset');
const box_t3_fieldsset = $('#pnl_schema3-table3-box_fieldsset');

const btn_t1_preview = $('#pnl_schema3-table1-btn_preview');
const btn_t2_preview = $('#pnl_schema3-table2-btn_preview');
const btn_t3_preview = $('#pnl_schema3-table3-btn_preview');

const btn_next = $('#pnl_schema3-btn_next');


const obj = {
	cbo_t1_tablename: $('#pnl_schema3-table1-cbo_tablename'),
	cbo_t1_field_txid: $('#pnl_schema3-table1-cbo_field_txid'),
	cbo_t1_field_txdate: $('#pnl_schema3-table1-cbo_field_txdate'),
	cbo_t1_field_txservicecharge: $('#pnl_schema3-cbo_field_txservicecharge'),
	cbo_t1_field_txisvoid: $('#pnl_schema3-table1-cbo_field_txisvoid'),
	chk_t1_schema_isverify : $('#pnl_schema3-table1-chk_schema_isverify'),

	cbo_t2_tablename: $('#pnl_schema3-table2-cbo_tablename'),
	cbo_t2_field_txid: $('#pnl_schema3-table2-cbo_field_txid'),
	cbo_t2_field_itemname: $('#pnl_schema3-table2-cbo_field_itemname'),
	cbo_t2_field_itemqty: $('#pnl_schema3-table2-cbo_field_itemqty'),
	cbo_t2_field_itemsubtotal: $('#pnl_schema3-table2-cbo_field_itemsubtotal'),
	cbo_t2_field_itemtax: $('#pnl_schema3-table2-cbo_field_itemtax'),
	chk_t2_schema_isverify : $('#pnl_schema3-table2-chk_schema_isverify'),

	cbo_t3_tablename: $('#pnl_schema3-table3-cbo_tablename'),
	cbo_t3_field_txid: $('#pnl_schema3-table3-cbo_field_txid'),
	cbo_t3_field_paymentname: $('#pnl_schema3-table3-cbo_field_paymentname'),
	cbo_t3_field_paymentvalue: $('#pnl_schema3-table3-cbo_field_paymentvalue'),
	chk_t3_schema_isverify: $('#pnl_schema3-table3-chk_schema_isverify'),
}	


let form = {}
var currentconfig = {};
var tablecbo = [];

const tabledata = {
	table1: {
		tableinfoel: $('#pnl_schema3-tableheader'),
		tablecboname: 'cbo_t1_tablename',
		fields: ['cbo_t1_field_txid', 'cbo_t1_field_txdate', 'cbo_t1_field_txservicecharge', 'cbo_t1_field_txisvoid'],
		fieldbox:box_t1_fieldsset
	},
	table2: {
		tableinfoel: $('#pnl_schema3-tabledetil'),
		tablecboname: 'cbo_t2_tablename',
		fields: ['cbo_t2_field_txid', 'cbo_t2_field_itemname', 'cbo_t2_field_itemqty', 'cbo_t2_field_itemsubtotal', 'cbo_t2_field_itemtax'],
		fieldbox: box_t2_fieldsset
	},
	
	table3: {
		tableinfoel: $('#pnl_schema3-tablepayment'),
		tablecboname: 'cbo_t3_tablename',
		fields: ['cbo_t3_field_txid', 'cbo_t3_field_paymentname', 'cbo_t3_field_paymentvalue'],
		fieldbox: box_t3_fieldsset
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

	btn_t1_preview.linkbutton({ onClick: () => { btn_preview_click('table1', obj.chk_t1_schema_isverify.checkbox('options')); } })
	btn_t2_preview.linkbutton({ onClick: () => { btn_preview_click('table2', obj.chk_t2_schema_isverify.checkbox('options')); } })
	btn_t3_preview.linkbutton({ onClick: () => { btn_preview_click('table3', obj.chk_t3_schema_isverify.checkbox('options')); } })

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
	box_t1_fieldsset.hide();
	obj.chk_t1_schema_isverify.checkbox('uncheck');
	ResetDetil();	
	form.setViewMode(false);
}


export function ResetDetil() {
	box_t2_fieldsset.hide();
	box_t3_fieldsset.hide();
	var fields = tabledata.table2.fields.concat( tabledata.table3.fields)
	for (var name of fields) {
		obj[name].combo('setValue', '');
		obj[name].combo('setText', '');
	}

	obj.cbo_t2_tablename.combo('disable');
	obj.cbo_t2_tablename.combo('setValue', '');
	obj.cbo_t2_tablename.combo('setText', '');
	
	obj.cbo_t3_tablename.combo('disable');
	obj.cbo_t3_tablename.combo('setValue', '');
	obj.cbo_t3_tablename.combo('setText', '');

	obj.chk_t2_schema_isverify.checkbox('uncheck');
	obj.chk_t3_schema_isverify.checkbox('uncheck');
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
		if (t2_tablename!='') {
			form.setValue(obj.cbo_t2_field_txid, t2_fields.txid, t2_fields.txid);
			form.setValue(obj.cbo_t2_field_itemname, t2_fields.itemname, t2_fields.itemname);
			form.setValue(obj.cbo_t2_field_itemqty, t2_fields.itemqty, t2_fields.itemqty);
			form.setValue(obj.cbo_t2_field_itemsubtotal, t2_fields.itemsubtotal, t2_fields.itemsubtotal);
			form.setValue(obj.cbo_t2_field_itemtax, t2_fields.itemtax, t2_fields.itemtax);
		}

		box_t3_fieldsset.hide();
		var t3_tablename = currentconfig.schema.payments.tablename;
		var t3_fields = currentconfig.schema.payments.fields;
		form.setValue(obj.cbo_t3_tablename, t3_tablename, t3_tablename);
		if (t2_tablename!='') {
			form.setValue(obj.cbo_t3_field_txid, t3_fields.txid, t3_fields.txid);
			form.setValue(obj.cbo_t3_field_paymentname, t3_fields.paymentname, t3_fields.paymentname);
			form.setValue(obj.cbo_t3_field_paymentvalue, t3_fields.paymentvalue, t3_fields.paymentvalue);
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

		box_t3_fieldsset.hide();
		obj.cbo_t3_tablename.combo('disable');
		form.setValue(obj.cbo_t2_tablename, '', '--PILIH--');
		form.setValue(obj.cbo_t3_field_txid, '', '--PILIH--');
		form.setValue(obj.cbo_t3_field_paymentname, '', '--PILIH--');
		form.setValue(obj.cbo_t3_field_paymentvalue, '', '--PILIH--');

	}

	obj.chk_t1_schema_isverify.checkbox('uncheck')
	obj.chk_t2_schema_isverify.checkbox('uncheck')
	obj.chk_t3_schema_isverify.checkbox('uncheck')

	form.setViewMode(false);
}

export async function verified(varifytable, checked) {
	var prevchk1 = obj.chk_t1_schema_isverify.checkbox('options');
	var prevchk2 = obj.chk_t2_schema_isverify.checkbox('options');
	var prevchk3 = obj.chk_t3_schema_isverify.checkbox('options');


	form.setViewMode(true);
	switch (varifytable) {
		case "table1" :
			obj.chk_t1_schema_isverify.checkbox(checked? 'check' : 'uncheck');
			if (checked) {
				obj.cbo_t2_tablename.combo(prevchk2.checked ? 'disable' : 'enable');
				obj.cbo_t2_field_txid.combo(prevchk2.checked ? 'disable' : 'enable');
				obj.cbo_t2_field_itemname.combo(prevchk2.checked ? 'disable' : 'enable');
				obj.cbo_t2_field_itemqty.combo(prevchk2.checked ? 'disable' : 'enable');
				obj.cbo_t2_field_itemsubtotal.combo(prevchk2.checked ? 'disable' : 'enable');
				obj.cbo_t2_field_itemtax.combo(prevchk2.checked ? 'disable' : 'enable');
				if (obj.cbo_t2_tablename.combo('getValue')!='') {
					box_t2_fieldsset.show();
				}

				obj.cbo_t3_tablename.combo(prevchk3.checked ? 'disable' : 'enable');
				obj.cbo_t3_field_txid.combo(prevchk3.checked ? 'disable' : 'enable');
				obj.cbo_t3_field_paymentname.combo(prevchk3.checked ? 'disable' : 'enable');
				obj.cbo_t3_field_paymentvalue.combo(prevchk3.checked ? 'disable' : 'enable');
				if (obj.cbo_t3_tablename.combo('getValue')!='') {
					box_t3_fieldsset.show();
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
		case "table3" :
			obj.chk_t3_schema_isverify.checkbox(checked? 'check' : 'uncheck');
			table3_lock(checked ? true : false);
			await save_configuration_payments(checked);
			break;				
	}
	form.setViewMode(false);


	var chk1 = obj.chk_t1_schema_isverify.checkbox('options');
	var chk2 = obj.chk_t2_schema_isverify.checkbox('options');
	var chk3 = obj.chk_t3_schema_isverify.checkbox('options');
	if (chk1.checked && chk2.checked && chk3.checked) {
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

		case 'table3' :
			tableinfo = {
				model: 'model-c',
				tablename: obj.cbo_t3_tablename.combo('getValue'),
				fields: {
					txid : obj.cbo_t3_field_txid.combo('getValue'),
					paymentname: obj.cbo_t3_field_paymentname.combo('getValue'),
					paymentvalue: obj.cbo_t3_field_paymentvalue.combo('getValue'),					
				}
			}
			break;			
	}



	var pnl_verify = $ui.getPages().ITEMS['pnl_verify'];
	pnl_verify.handler.setVerifierFor({
		currconf: currentconfig,
		panelname: 'pnl_schema3', 
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


async function save_configuration_payments(checked) {
	currentconfig.schema.payments.tablename = obj.cbo_t3_tablename.combo('getValue');
	currentconfig.schema.payments.isverified = checked;
	Object.assign(currentconfig.schema.payments.fields, {
		txid: obj.cbo_t3_field_txid.combo('getValue'),
		paymentname: obj.cbo_t3_field_paymentname.combo('getValue'),
		paymentvalue: obj.cbo_t3_field_paymentvalue.combo('getValue'),
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


function table3_lock(lock) {
	obj.cbo_t3_tablename.combo(lock ? 'disable' : 'enable');
	obj.cbo_t3_field_txid.combo(lock ? 'disable' : 'enable');
	obj.cbo_t3_field_paymentname.combo(lock ? 'disable' : 'enable');
	obj.cbo_t3_field_paymentvalue.combo(lock ? 'disable' : 'enable');
}


function btn_next_click() {
	var pnl_final = $ui.getPages().ITEMS['pnl_final'];
	pnl_final.handler.setFinalizedFor({
		currconf: currentconfig,
		panelname: 'pnl_schema3', 
	});
	$ui.getPages().show(pnl_final, (pnl) => {
		$ui.setPanelShowingInfo(pnl.id);
	});
}