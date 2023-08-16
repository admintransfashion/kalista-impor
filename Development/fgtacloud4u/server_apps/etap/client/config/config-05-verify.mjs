var this_page_id;
var this_page_options;

import {fgta4slideselect2} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect2.mjs'



const boxes_verify = $('.pnl_verify-box_verify');

const box_model_a = $('#pnl_verify-box_model_a');
const box_model_b = $('#pnl_verify-box_model_b');
const box_model_c = $('#pnl_verify-box_model_c');
const box_model_d = $('#pnl_verify-box_model_d');


const tbl_model_a = $('#pnl_verify-tbl_model_a');
const tbl_model_b = $('#pnl_verify-tbl_model_b');
const tbl_model_c = $('#pnl_verify-tbl_model_c');
const tbl_model_d = $('#pnl_verify-tbl_model_d');

const txt_title = $('#pnl_verify-txt_title');
const pnl_form = $('#pnl_verify-form');
const btn_load = $('#pnl_verify-btn_load');
const btn_verify = $('#pnl_verify-btn_verify');
const txt_verifynote = $('#pnl_verify-txt_verifynote');

const obj = {
	chk_table_isverify : $('#pnl_verify-chk_table_isverify'),
	cbo_limit: $('#pnl_verify-cbo_limit'),
}


const drivers = window.parent.LocalRequire('./drivers.js');




let form = {}
let grd_list_a = {}
let grd_list_b = {}
let grd_list_c = {}
let grd_list_d = {}

var this_back_panelname;
var this_back_verifytable;
var this_tableinfotobeverify;
var this_already_verified;

var currentconfig = {};
var unverified;



export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;

	form = new global.fgta4form(pnl_form, {
		objects : obj,
	});


	grd_list_a = new global.fgta4grid(tbl_model_a, {})
	grd_list_b = new global.fgta4grid(tbl_model_b, {})
	grd_list_c = new global.fgta4grid(tbl_model_c, {})
	grd_list_d = new global.fgta4grid(tbl_model_d, {})


	new fgta4slideselect2(obj.cbo_limit, {
		title: 'Pilih Limit baris data ',
		returnpage: this_page_id,
		
		fieldValue: 'limit_id',
		fieldValueMap: 'limit_name',
		fieldDisplay: 'limit_name',
		fields: [
			{mapping: 'limit_id', text: 'limit_id'},
			{mapping: 'limit_name', text: 'limit_name'},
		],
		OnDataLoading: async () => {
			return [
				{limit_id:10, limit_name:10},
				{limit_id:30, limit_name:30},
				{limit_id:100, limit_name:100},
				{limit_id:200, limit_name:200},
				{limit_id:500, limit_name:500},
				{limit_id:1000, limit_name:1000},
				{limit_id:5000, limit_name:5000},
				{limit_id:10000, limit_name:10000}
			]
		},
		OnDataLoaded : async (result, options) => {},
		OnSelected: async (value, display, record) => {
		}
	});

	btn_verify.linkbutton({onClick: () => { btn_verify_click() }});
	btn_load.linkbutton({onClick: () => { btn_load_click() }});

	obj.chk_table_isverify.checkbox({onChange: (checked) => { chk_table_isverify_checked(checked) 	}});

	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show(this_back_panelname, (pnl)=>{
				$ui.setPanelShowingInfo(pnl.id);
				var chk = obj.chk_table_isverify.checkbox('options');
				pnl.handler.verified(this_back_verifytable, chk.checked);
			})
		};			

	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
		};			
	});

}


export function setVerifierFor(param) {

	currentconfig = param.currconf;

	txt_title.html(param.verifytitle);
	this_back_panelname = param.panelname;
	this_back_verifytable = param.tableid;
	
	txt_verifynote.html(param.verifynote);
	this_tableinfotobeverify = param.tableinfo;


	unverified = false;

	box_model_a.hide();
	box_model_b.hide();
	box_model_c.hide();
	box_model_d.hide();
	switch (this_tableinfotobeverify.model) {
		case 'model-a' : box_model_a.show(); break;
		case 'model-b' : box_model_b.show(); break;
		case 'model-c' : box_model_c.show(); break;
		case 'model-d' : box_model_d.show(); break;
	}
}

export function Reset(param) {
	this_already_verified = param.already_verified;
	if (param.already_verified) {
		btn_verify.linkbutton('enable');
		obj.chk_table_isverify.checkbox('check');
		obj.chk_table_isverify.object_isdisabled = false;
		obj.chk_table_isverify.checkbox('enable');
	} else {
		btn_verify.linkbutton('disable');
		obj.chk_table_isverify.checkbox('uncheck');
		obj.chk_table_isverify.object_isdisabled = true;
		obj.chk_table_isverify.checkbox('disable');
	}
	form.setValue(obj.cbo_limit, '10', '10');
	form.setViewMode(false);

	grd_list_a.clear();
	grd_list_b.clear();
	grd_list_c.clear();
	grd_list_d.clear();

	boxes_verify.hide();
	
}


function btn_verify_click() {
	var chk = obj.chk_table_isverify.checkbox('options');
	$ui.getPages().show(this_back_panelname, (pnl) => {
		$ui.setPanelShowingInfo(pnl.id);
		pnl.handler.verified(this_back_verifytable, chk.checked);
	});
}

function chk_table_isverify_checked(checked) {
	if (checked) {
		btn_verify.linkbutton('enable');
	} else {
		if (this_already_verified && this_back_verifytable=='table1' && this_back_panelname!="pnl_schema1") {
			$ui.ShowMessage("[WARNING]Un-Verifikasi akan menyebabkan konfigurasi/setting untuk pemilihan kembali ke posisi kosong. Pakah anda yakin?", {
				'Ok': () => {
					btn_verify.linkbutton('disable');
				},
				'Cancel': () => {
					obj.chk_table_isverify.checkbox('check');	
				}
			})
		} else {
			btn_verify.linkbutton('disable');
		}
		unverified = true;

	}
}


function btn_load_click() {

		;(async (fn_dataloaded) => {
			try {
				var drivername = currentconfig.dbtype;
				var driverpath = drivers.getdriverpath(drivername);
			
				var drv = window.parent.LocalRequire(driverpath)(currentconfig);
				try {
					await drv.connect();
					
					// query dapatkan table;
					this_tableinfotobeverify.limit = obj.cbo_limit.combo('getValue');
					var rows = await drv.previewtable(this_tableinfotobeverify);

					if (typeof fn_dataloaded==='function') {
						fn_dataloaded(rows);
					}
		
				} 	catch (err) {
					throw err;
				} finally {
					await drv.close();
				}
			} catch (err) {
				console.error(err);
				$ui.ShowMessage('[ERROR]'+err.message);
			}
		})((rows) => {
			switch (this_tableinfotobeverify.model) {
				case 'model-a' : loaddata_table_a(rows); break;
				case 'model-b' : loaddata_table_b(rows); break;
				case 'model-c' : loaddata_table_c(rows); break;
				case 'model-d' : loaddata_table_d(rows); break;
			}

			// finally
			obj.chk_table_isverify.object_isdisabled = false;
			obj.chk_table_isverify.checkbox('enable');
			form.setViewMode(false);

			boxes_verify.show();

		});
	


}


function loaddata_table_a(rows) {
	grd_list_a.clear();
	grd_list_a.fill(rows);
}


function loaddata_table_b(rows) {
	grd_list_b.clear();
	grd_list_b.fill(rows);
}


function loaddata_table_c(rows) {
	grd_list_c.clear();
	grd_list_c.fill(rows);
}


function loaddata_table_d(rows) {
	grd_list_d.clear();
	grd_list_d.fill(rows);
}