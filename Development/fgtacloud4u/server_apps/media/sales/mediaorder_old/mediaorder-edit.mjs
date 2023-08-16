var this_page_id;
var this_page_options;

import {fgta4slideselect} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'

const btn_edit = $('#pnl_edit-btn_edit');
const btn_save = $('#pnl_edit-btn_save');
const btn_delete = $('#pnl_edit-btn_delete');
const btn_print = $('#pnl_edit-btn_print');
const btn_printlp = $('#pnl_edit-btn_printlp');
const btn_commit = $('#pnl_edit-btn_commit');
const btn_uncommit = $('#pnl_edit-btn_uncommit')
const btn_close = $('#pnl_edit-btn_close');
const btn_reopen = $('#pnl_edit-btn_reopen')


const pnl_form = $('#pnl_edit-form')
const obj = {
	txt_mediaorder_id: $('#pnl_edit-txt_mediaorder_id'),
	txt_mediaorder_descr: $('#pnl_edit-txt_mediaorder_descr'),
	dt_mediaorder_date: $('#pnl_edit-dt_mediaorder_date'),
	chk_mediaorder_iscommit : $('#pnl_edit-chk_mediaorder_iscommit'),
	chk_mediaorder_iscompleted : $('#pnl_edit-chk_mediaorder_iscompleted'),
	chk_mediaorder_isbilled: $('#pnl_edit-chk_mediaorder_isbilled'),
	chk_mediaorder_isclosed: $('#pnl_edit-chk_mediaorder_isclosed'),
	cbo_mediaordertype_id: $('#pnl_edit-cbo_mediaordertype_id'),
	cbo_agency_partner_id: $('#pnl_edit-cbo_agency_partner_id'),
	cbo_advertiser_partner_id: $('#pnl_edit-cbo_advertiser_partner_id'),
	cbo_brand_id: $('#pnl_edit-cbo_brand_id'),
	cbo_mediapackage_id: $('#pnl_edit-cbo_mediapackage_id'),
	cbo_salesordertype_id: $('#pnl_edit-cbo_salesordertype_id'),
	cbo_doc_id: $('#pnl_edit-cbo_doc_id')
}


const rec_commitby = $('#pnl_edit_record-commitby');
const rec_commitdate = $('#pnl_edit_record-commitdate');
const rec_closeby = $('#pnl_edit_record-closeby');
const rec_closedate = $('#pnl_edit_record-closedate');


let form = {}

export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;


	var disableedit = false;
	// switch (this_page_options.variancename) {
	// 	case 'commit' :
	//		disableedit = true;
	//		btn_edit.linkbutton('disable');
	//		btn_save.linkbutton('disable');
	//		btn_delete.linkbutton('disable');
	//		break;
	// }


	form = new global.fgta4form(pnl_form, {
		primary: obj.txt_mediaorder_id,
		autoid: true,
		logview: 'trn_mediaorder',
		btn_edit: disableedit==true? $('<a>edit</a>') : btn_edit,
		btn_save: disableedit==true? $('<a>save</a>') : btn_save,
		btn_delete: disableedit==true? $('<a>delete</a>') : btn_delete,		
		objects : obj,
		OnDataSaving: async (data, options) => { await form_datasaving(data, options) },
		OnDataSaveError: async (data, options) => { await form_datasaveerror(data, options) },
		OnDataSaved: async (result, options) => {  await form_datasaved(result, options) },
		OnDataDeleting: async (data, options) => { await form_deleting(data, options) },
		OnDataDeleted: async (result, options) => { await form_deleted(result, options) },
		OnIdSetup : (options) => { form_idsetup(options) },
		OnViewModeChanged : (viewonly) => { form_viewmodechanged(viewonly) },
		OnRecordStatusCreated: () => {
			$('#pnl_edit_record_custom').detach().appendTo("#pnl_edit_record");
			$('#pnl_edit_record_custom').show();
		}		
	})



	new fgta4slideselect(obj.cbo_mediaordertype_id, {
		title: 'Pilih mediaordertype_id',
		returnpage: this_page_id,
		api: $ui.apis.load_mediaordertype_id,
		fieldValue: 'mediaordertype_id',
		fieldValueMap: 'mediaordertype_id',
		fieldDisplay: 'mediaordertype_name',
		fields: [
			{mapping: 'mediaordertype_id', text: 'mediaordertype_id'},
			{mapping: 'mediaordertype_name', text: 'mediaordertype_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	})				
				
	new fgta4slideselect(obj.cbo_agency_partner_id, {
		title: 'Pilih agency_partner_id',
		returnpage: this_page_id,
		api: $ui.apis.load_agency_partner_id,
		fieldValue: 'agency_partner_id',
		fieldValueMap: 'partner_id',
		fieldDisplay: 'partner_name',
		fields: [
			{mapping: 'partner_id', text: 'partner_id'},
			{mapping: 'partner_name', text: 'partner_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	})				
				
	new fgta4slideselect(obj.cbo_advertiser_partner_id, {
		title: 'Pilih advertiser_partner_id',
		returnpage: this_page_id,
		api: $ui.apis.load_advertiser_partner_id,
		fieldValue: 'advertiser_partner_id',
		fieldValueMap: 'partner_id',
		fieldDisplay: 'partner_name',
		fields: [
			{mapping: 'partner_id', text: 'partner_id'},
			{mapping: 'partner_name', text: 'partner_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	})				
				
	new fgta4slideselect(obj.cbo_brand_id, {
		title: 'Pilih brand_id',
		returnpage: this_page_id,
		api: $ui.apis.load_brand_id,
		fieldValue: 'brand_id',
		fieldValueMap: 'brand_id',
		fieldDisplay: 'brand_name',
		fields: [
			{mapping: 'brand_id', text: 'brand_id'},
			{mapping: 'brand_name', text: 'brand_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	})				
				
	new fgta4slideselect(obj.cbo_mediapackage_id, {
		title: 'Pilih mediapackage_id',
		returnpage: this_page_id,
		api: $ui.apis.load_mediapackage_id,
		fieldValue: 'mediapackage_id',
		fieldValueMap: 'mediapackage_id',
		fieldDisplay: 'mediapackage_descr',
		fields: [
			{mapping: 'mediapackage_id', text: 'mediapackage_id'},
			{mapping: 'mediapackage_descr', text: 'mediapackage_descr'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
			result.records.unshift({mediapackage_id:'--NULL--', mediapackage_descr:'NONE'});	
		},
		OnSelected: (value, display, record) => {}
	})				
				
	new fgta4slideselect(obj.cbo_salesordertype_id, {
		title: 'Pilih salesordertype_id',
		returnpage: this_page_id,
		api: $ui.apis.load_salesordertype_id,
		fieldValue: 'salesordertype_id',
		fieldValueMap: 'salesordertype_id',
		fieldDisplay: 'salesordertype_name',
		fields: [
			{mapping: 'salesordertype_id', text: 'salesordertype_id'},
			{mapping: 'salesordertype_name', text: 'salesordertype_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	})				
				
	new fgta4slideselect(obj.cbo_doc_id, {
		title: 'Pilih doc_id',
		returnpage: this_page_id,
		api: $ui.apis.load_doc_id,
		fieldValue: 'doc_id',
		fieldValueMap: 'doc_id',
		fieldDisplay: 'doc_name',
		fields: [
			{mapping: 'doc_id', text: 'doc_id'},
			{mapping: 'doc_name', text: 'doc_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	})				
				


	btn_print.linkbutton({ onClick: () => { btn_print_click(); } });	
	btn_printlp.linkbutton({ onClick: () => { btn_print_click('lp'); } });	
	btn_commit.linkbutton({ onClick: () => { btn_commit_click() }});
	btn_uncommit.linkbutton({ onClick: () => { btn_uncommit_click() }});
	btn_close.linkbutton({ onClick: () => { btn_close_click() }});
	btn_reopen.linkbutton({ onClick: () => { btn_reopen_click() }});
	


	document.addEventListener('keydown', (ev)=>{
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if (ev.code=='KeyS' && ev.ctrlKey==true) {
				if (!form.isInViewMode()) {
					form.btn_save_click();
				}
				ev.stopPropagation()
				ev.preventDefault()
			}
		}
	}, true)
	
	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	})	

	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			if (form.isDataChanged()) {
				form.canceledit(()=>{
					$ui.getPages().show('pnl_list', ()=>{
						form.setViewMode()
						$ui.getPages().ITEMS['pnl_list'].handler.scrolllast()
					})
				})
			} else {
				$ui.getPages().show('pnl_list', ()=>{
					form.setViewMode()
					$ui.getPages().ITEMS['pnl_list'].handler.scrolllast()
				})
			}
		
		}
	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if (form.isDataChanged()) {
				ev.detail.cancel = true;
				$ui.ShowMessage('Anda masih dalam mode edit dengan pending data, silakan matikan mode edit untuk kembali ke halaman utama.')
			}
		}
	})



}


export function OnSizeRecalculated(width, height) {
}




export function open(data, rowid, viewmode=true, fn_callback) {


	var fn_dataopening = async (options) => {
		options.criteria[form.primary.mapping] = data[form.primary.mapping]
	}

	var fn_dataopened = async (result, options) => {

		if (result.record.mediapackage_id==null) { result.record.mediapackage_id='--NULL--'; result.record.mediapackage_descr='NONE'; }

		rec_commitby.html(result.record.mediaorder_commitby_username);
		rec_commitdate.html(result.record.mediaorder_commitdate);
		rec_closeby.html(result.record.mediaorder_closeby_username);
		rec_closedate.html(result.record.mediaorder_closedate);

		form.SuspendEvent(true);
		form
			.fill(result.record)
			.setValue(obj.cbo_mediaordertype_id, result.record.mediaordertype_id, result.record.mediaordertype_name)
			.setValue(obj.cbo_agency_partner_id, result.record.agency_partner_id, result.record.agency_partner_name)
			.setValue(obj.cbo_advertiser_partner_id, result.record.advertiser_partner_id, result.record.advertiser_partner_name)
			.setValue(obj.cbo_brand_id, result.record.brand_id, result.record.brand_name)
			.setValue(obj.cbo_mediapackage_id, result.record.mediapackage_id, result.record.mediapackage_descr)
			.setValue(obj.cbo_salesordertype_id, result.record.salesordertype_id, result.record.salesordertype_name)
			.setValue(obj.cbo_doc_id, result.record.doc_id, result.record.doc_name)
			.setValue(obj.chk_mediaorder_iscommit, result.record.mediaorder_iscommit)
			.setValue(obj.chk_mediaorder_iscompleted, result.record.mediaorder_iscompleted)
			.setValue(obj.chk_mediaorder_isbilled, result.record.mediaorder_isbilled)
			.setValue(obj.chk_mediaorder_isclosed, result.record.mediaorder_isclosed)

			.commit()
			.setViewMode(viewmode)
			.lock(false)
			.rowid = rowid

		// tampilkan form untuk data editor
		fn_callback()
		form.SuspendEvent(false);


		if (result.record.mediaorder_iscommit==1 || result.record.mediaorder_iscompleted==1 || result.record.mediaorder_isbilled==1 || result.record.mediaorder_isclosed==1) {
			form.lock(true);
		} else {
			form.lock(false)
		}



		if (result.record.mediaorder_isclosed==1) {
			btn_close.linkbutton('disable');
			btn_reopen.linkbutton('enable');
		} else {
			btn_close.linkbutton('enable');
			btn_reopen.linkbutton('disable');
		}

		if (result.record.mediaorder_iscommit==1) {
			btn_commit.linkbutton('disable');
			btn_uncommit.linkbutton('enable');
		} else {
			btn_commit.linkbutton('enable');
			btn_uncommit.linkbutton('disable');
		}


		// final
		if (result.record.mediaorder_iscompleted==1 || result.record.mediaorder_isbilled==1 || result.record.mediaorder_isclosed==1) {
			btn_commit.linkbutton('disable');
			btn_uncommit.linkbutton('disable');
		}		

	}

	var fn_dataopenerror = (err) => {
		$ui.ShowMessage('[ERROR]'+err.errormessage);
	}

	form.dataload(fn_dataopening, fn_dataopened, fn_dataopenerror)
	
}


export function createnew() {
	form.createnew(async (data, options)=>{
		// console.log(data)
		// console.log(options)
		form.rowid = null

		// set nilai-nilai default untuk form
			data.mediaorder_date = global.now()

			data.mediaordertype_id = '0'
			data.mediaordertype_name = '-- PILIH --'
			data.agency_partner_id = '0'
			data.partner_name = '-- PILIH --'
			data.advertiser_partner_id = '0'
			data.partner_name = '-- PILIH --'
			data.brand_id = '0'
			data.brand_name = '-- PILIH --'
			data.mediapackage_id = '--NULL--'
			data.mediapackage_descr = 'NONE'
			data.salesordertype_id = 'MO'
			data.salesordertype_name = 'MEDIA ORDER'
			data.doc_id = 'MEDIAORDER'
			data.doc_name = 'MEDIAORDER'



		options.OnCanceled = () => {
			$ui.getPages().show('pnl_list')
		}

		$ui.getPages().ITEMS['pnl_edititemsgrid'].handler.createnew(data, options)
		$ui.getPages().ITEMS['pnl_editpaytermgrid'].handler.createnew(data, options)


	})
}


export function detil_open(pnlname) {
	if (form.isDataChanged()) {
		$ui.ShowMessage('Simpan dulu perubahan datanya.')
		return;
	}

	//$ui.getPages().show(pnlname)
	$ui.getPages().show(pnlname, () => {
		$ui.getPages().ITEMS[pnlname].handler.OpenDetil(form.getData())
	})	
}


function form_viewmodechanged(viewmode) {
	var OnViewModeChangedEvent = new CustomEvent('OnViewModeChanged', {detail: {}})
	$ui.triggerevent(OnViewModeChangedEvent, {
		viewmode: viewmode
	})
}

function form_idsetup(options) {
	var objid = obj.txt_mediaorder_id
	switch (options.action) {
		case 'fill' :
			objid.textbox('disable') 
			break;

		case 'createnew' :
			// console.log('new')
			if (form.autoid) {
				objid.textbox('disable') 
				objid.textbox('setText', '[AUTO]') 
			} else {
				objid.textbox('enable') 
			}
			break;
			
		case 'save' :
			objid.textbox('disable') 
			break;	
	}
}


async function form_datasaving(data, options) {
	// cek dulu data yang akan disimpan,
	// apabila belum sesuai dengan yang diharuskan, batalkan penyimpanan
	//    options.cancel = true

	// Modifikasi object data, apabila ingin menambahkan variabel yang akan dikirim ke server

	options.skipmappingresponse = ["mediapackage_id", "agency_partner_id", "advertiser_partner_id"];

}

async function form_datasaveerror(err, options) {
	// apabila mau olah error messagenya
	// $ui.ShowMessage(err.errormessage)
	console.log(err)
}


async function form_datasaved(result, options) {
	// Apabila tidak mau munculkan dialog
	// options.suppressdialog = true

	// Apabila ingin mengganti message Data Tersimpan
	// options.savedmessage = 'Data sudah disimpan cuy!'

	// if (form.isNewData()) {
	// 	console.log('masukan ke grid')
	// 	$ui.getPages().ITEMS['pnl_list'].handler.updategrid(form.getData())
	// } else {
	// 	console.log('update grid')
	// }


	var data = {}
	Object.assign(data, form.getData(), result.dataresponse)

	form.setValue(obj.cbo_mediapackage_id, result.dataresponse.mediapackage_descr!=='--NULL--' ? result.dataresponse.mediapackage_id : '--NULL--', result.dataresponse.mediapackage_descr!=='--NULL--'?result.dataresponse.mediapackage_descr:'NONE')
	form.setValue(obj.cbo_agency_partner_id, result.dataresponse.agency_partner_id, result.dataresponse.agency_partner_name)
	form.setValue(obj.cbo_advertiser_partner_id, result.dataresponse.advertiser_partner_id, result.dataresponse.advertiser_partner_name)

	form.rowid = $ui.getPages().ITEMS['pnl_list'].handler.updategrid(data, form.rowid)
}



async function form_deleting(data) {
}

async function form_deleted(result, options) {
	$ui.getPages().show('pnl_list')
	$ui.getPages().ITEMS['pnl_list'].handler.removerow(form.rowid)

}




function btn_print_click(doc) {

	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('Simpan dulu perubahan datanya.');
		return;
	}

	var id = obj.txt_mediaorder_id.textbox('getValue');
	var printurl = 'index.php/printout/' + window.global.modulefullname + '/mediaorder.xprint?id=' + id;
	if (doc=='lp') {
		printurl = 'index.php/printout/' + window.global.modulefullname + '/mediaorder.xprint-lp?id=' + id;
	}


	var debug = false;
	if (debug) {
		var w = window.open(printurl);
		w.onload = () => {
			window.onreadytoprint(() => {
				iframe.contentWindow.print();
			});
		}
	} else {
		$ui.mask('wait...');
		var iframe_id = 'fgta_printelement';
		var iframe = document.getElementById(iframe_id);
		if (iframe) {
			iframe.parentNode.removeChild(iframe);
			iframe = null;
		}

		if (!iframe) {
			iframe = document.createElement('iframe');
			iframe.id = iframe_id;
			iframe.style.visibility = 'hidden';
			iframe.style.height = '10px';
			iframe.style.widows = '10px';
			document.body.appendChild(iframe);

			iframe.onload = () => {
				$ui.unmask();
				iframe.contentWindow.OnPrintCommand(() => {
					console.log('start print');
					iframe.contentWindow.print();
				});
				iframe.contentWindow.preparemodule();
			}
		}
		iframe.src = printurl + '&iframe=1';

	}

}	

function btn_commit_click() {
	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('[WARNING]Simpan dulu perubahan data, dan tidak sedang dalam mode edit.');
		return;
	}

	var mediaorder_id = obj.txt_mediaorder_id.textbox('getText');
	$ui.ShowMessage("[QUESTION]Apakah anda yakin akan commit '" + mediaorder_id + "' ?", {
		"OK": async () => {
			$ui.mask('wait..');
			var { Flags } = await import('./mediaorder.xtion-flags.mjs');
			Flags('xtion-commit', mediaorder_id, (err, success) => {
				if (success) {
					form.lock(true);
					$ui.ShowMessage("[INFO]Media Order '" + mediaorder_id + "' telah di commit");
					btn_commit.linkbutton('disable');
					btn_uncommit.linkbutton('enable');
					obj.chk_mediaorder_iscommit.checkbox('check');
					$ui.getPages().ITEMS['pnl_list'].handler.updategrid({mediaorder_iscommit:1}, form.rowid)
					form.commit();
				}
			});
		},
		"Cancel": () => {
		}
	});
}


function btn_uncommit_click() {
	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('[WARNING]Simpan dulu perubahan data, dan tidak sedang dalam mode edit.');
		return;
	}

	var mediaorder_id = obj.txt_mediaorder_id.textbox('getText');
	$ui.ShowMessage("[QUESTION]Apakah anda yakin akan uncommit '" + mediaorder_id + "' ?", {
		"OK": async () => {
			$ui.mask('wait..');
			var { Flags } = await import('./mediaorder.xtion-flags.mjs');
			Flags('xtion-uncommit', mediaorder_id, (err, success) => {
				if (success) {
					$ui.ShowMessage("[INFO]Media Order '" + mediaorder_id + "' telah di un-commit");
					btn_commit.linkbutton('enable');
					btn_uncommit.linkbutton('disable');
					obj.chk_mediaorder_iscommit.checkbox('uncheck');
					$ui.getPages().ITEMS['pnl_list'].handler.updategrid({mediaorder_iscommit:0}, form.rowid)
					form.commit();
				}
			});
		},
		"Cancel": () => {
		}
	});
}


function btn_close_click() {
	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('[WARNING]Simpan dulu perubahan data, dan tidak sedang dalam mode edit.');
		return;
	}

	
	var mediaorder_id = obj.txt_mediaorder_id.textbox('getText');
	$ui.ShowMessage("[QUESTION]Apakah anda yakin akan close '" + mediaorder_id + "' ?", {
		"OK": async () => {
			$ui.mask('wait..');
			var { Flags } = await import('./mediaorder.xtion-flags.mjs');
			Flags('xtion-close', mediaorder_id, (err, success) => {
				if (success) {
					form.lock(true);
					$ui.ShowMessage("[INFO]Media Order '" + mediaorder_id + "' telah di close");
					btn_close.linkbutton('disable');
					btn_reopen.linkbutton('enable');
					btn_commit.linkbutton('disable');
					btn_uncommit.linkbutton('disable');
					obj.chk_mediaorder_isclosed.checkbox('check');
					$ui.getPages().ITEMS['pnl_list'].handler.updategrid({mediaorder_isclosed:1}, form.rowid)
					form.commit();
				}
			});
		},
		"Cancel": () => {
		}
	});
}


function btn_reopen_click() {
	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('[WARNING]Simpan dulu perubahan data, dan tidak sedang dalam mode edit.');
		return;
	}

	var isbilled = obj.chk_mediaorder_isbilled.checkbox('options');
	var iscomplete = obj.chk_mediaorder_iscompleted.checkbox('options');
	var iscommit = obj.chk_mediaorder_iscommit.checkbox('options');
	var mediaorder_id = obj.txt_mediaorder_id.textbox('getText');
	$ui.ShowMessage("[QUESTION]Apakah anda yakin akan reopen '" + mediaorder_id + "' ?", {
		"OK": async () => {
			$ui.mask('wait..');
			var { Flags } = await import('./mediaorder.xtion-flags.mjs');
			Flags('xtion-reopen', mediaorder_id, (err, success) => {
				if (success) {
					$ui.ShowMessage("[INFO]Media Order '" + mediaorder_id + "' telah di reopen");
					btn_close.linkbutton('enable');
					btn_reopen.linkbutton('disable');
					if (iscommit.checked) {
						btn_commit.linkbutton('disable');
						btn_uncommit.linkbutton('enable');
					} else {
						btn_commit.linkbutton('enable');
						btn_uncommit.linkbutton('disable');
					}

					if (iscomplete.checked || isbilled.checked ) {
						btn_commit.linkbutton('disable');
						btn_uncommit.linkbutton('disable');
					}

					obj.chk_mediaorder_isclosed.checkbox('uncheck');
					$ui.getPages().ITEMS['pnl_list'].handler.updategrid({mediaorder_isclosed:0}, form.rowid)
					form.commit();
				}
			});
		},
		"Cancel": () => {
		}
	});
}
