var this_page_id;
var this_page_options;

import {fgta4slideselect} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'

const btn_edit = $('#pnl_edit-btn_edit')
const btn_save = $('#pnl_edit-btn_save')
const btn_delete = $('#pnl_edit-btn_delete')





const pnl_form = $('#pnl_edit-form')
const obj = {
	txt_medialogproofitem_id: $('#pnl_edit-txt_medialogproofitem_id'),
	txt_mediaadslot_timestart: $('#pnl_edit-txt_mediaadslot_timestart'),
	txt_mediaadslot_timeend: $('#pnl_edit-txt_mediaadslot_timeend'),
	txt_mediaadslot_descr: $('#pnl_edit-txt_mediaadslot_descr'),
	txt_actual_timestart: $('#pnl_edit-txt_actual_timestart'),
	txt_actual_timeend: $('#pnl_edit-txt_actual_timeend'),
	txt_actual_duration: $('#pnl_edit-txt_actual_duration'),
	txt_spot_id: $('#pnl_edit-txt_spot_id'),
	txt_mediaorderitem_validr: $('#pnl_edit-txt_mediaorderitem_validr'),
	txt_mediaorderitem_ppnidr: $('#pnl_edit-txt_mediaorderitem_ppnidr'),
	cbo_pph_taxtype_id: $('#pnl_edit-cbo_pph_taxtype_id'),
	cbo_mediaorder_id: $('#pnl_edit-cbo_mediaorder_id'),
	cbo_mediaorderitem_id: $('#pnl_edit-cbo_mediaorderitem_id'),
	cbo_projbudget_id: $('#pnl_edit-cbo_projbudget_id'),
	cbo_projbudgettask_id: $('#pnl_edit-cbo_projbudgettask_id'),
	cbo_billoutpreprocess_id: $('#pnl_edit-cbo_billoutpreprocess_id'),
	txt_mediaordertype_id: $('#pnl_edit-txt_mediaordertype_id'),
	txt_logproof_partnerinfo: $('#pnl_edit-txt_logproof_partnerinfo'),
	txt_agency_partner_id: $('#pnl_edit-txt_agency_partner_id'),
	txt_advertiser_partner_id: $('#pnl_edit-txt_advertiser_partner_id'),
	txt_brand_id: $('#pnl_edit-txt_brand_id'),
	txt_project_id: $('#pnl_edit-txt_project_id'),
	txt_projecttask_id: $('#pnl_edit-txt_projecttask_id'),
	txt_medialogproof_id: $('#pnl_edit-txt_medialogproof_id')
}




let form;

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
		primary: obj.txt_medialogproofitem_id,
		autoid: true,
		logview: 'trn_medialogproofitem',
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
			undefined			
		}		
	})








	new fgta4slideselect(obj.cbo_pph_taxtype_id, {
		title: 'Pilih pph_taxtype_id',
		returnpage: this_page_id,
		api: $ui.apis.load_pph_taxtype_id,
		fieldValue: 'pph_taxtype_id',
		fieldValueMap: 'taxtype_id',
		fieldDisplay: 'taxtype_name',
		fields: [
			{mapping: 'taxtype_id', text: 'taxtype_id'},
			{mapping: 'taxtype_name', text: 'taxtype_name'},
		],
		OnDataLoading: (criteria) => {
						
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({taxtype_id:'--NULL--', taxtype_name:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {				
			}
		}
	})				
				
	new fgta4slideselect(obj.cbo_mediaorder_id, {
		title: 'Pilih mediaorder_id',
		returnpage: this_page_id,
		api: $ui.apis.load_mediaorder_id,
		fieldValue: 'mediaorder_id',
		fieldValueMap: 'mediaorder_id',
		fieldDisplay: 'mediaorder_descr',
		fields: [
			{mapping: 'mediaorder_id', text: 'mediaorder_id'},
			{mapping: 'mediaorder_descr', text: 'mediaorder_descr'},
		],
		OnDataLoading: (criteria) => {
						
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({mediaorder_id:'--NULL--', mediaorder_descr:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {				
			}
		}
	})				
				
	new fgta4slideselect(obj.cbo_mediaorderitem_id, {
		title: 'Pilih mediaorderitem_id',
		returnpage: this_page_id,
		api: $ui.apis.load_mediaorderitem_id,
		fieldValue: 'mediaorderitem_id',
		fieldValueMap: 'mediaorderitem_id',
		fieldDisplay: 'mediaorderitem_descr',
		fields: [
			{mapping: 'mediaorderitem_id', text: 'mediaorderitem_id'},
			{mapping: 'mediaorderitem_descr', text: 'mediaorderitem_descr'},
		],
		OnDataLoading: (criteria) => {
						
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({mediaorderitem_id:'--NULL--', mediaorderitem_descr:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {				
			}
		}
	})				
				
	new fgta4slideselect(obj.cbo_projbudget_id, {
		title: 'Pilih projbudget_id',
		returnpage: this_page_id,
		api: $ui.apis.load_projbudget_id,
		fieldValue: 'projbudget_id',
		fieldValueMap: 'projbudget_id',
		fieldDisplay: 'projbudget_name',
		fields: [
			{mapping: 'projbudget_id', text: 'projbudget_id'},
			{mapping: 'projbudget_name', text: 'projbudget_name'},
		],
		OnDataLoading: (criteria) => {
						
		},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
				form.setValue(obj.txt_project_id, record.project_id);			
										
			}
		}
	})				
				
	new fgta4slideselect(obj.cbo_projbudgettask_id, {
		title: 'Pilih projbudgettask_id',
		returnpage: this_page_id,
		api: $ui.apis.load_projbudgettask_id,
		fieldValue: 'projbudgettask_id',
		fieldValueMap: 'projbudgettask_id',
		fieldDisplay: 'projbudgettask_name',
		fields: [
			{mapping: 'projbudgettask_id', text: 'projbudgettask_id'},
			{mapping: 'projbudgettask_name', text: 'projbudgettask_name'},
		],
		OnDataLoading: (criteria) => {
			criteria.projbudget_id = form.getValue(obj.cbo_projbudget_id);			
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({projbudgettask_id:'--NULL--', projbudgettask_name:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
				form.setValue(obj.txt_projecttask_id, record.projecttask_id);			
										
			}
		}
	})				
				
	new fgta4slideselect(obj.cbo_billoutpreprocess_id, {
		title: 'Pilih billoutpreprocess_id',
		returnpage: this_page_id,
		api: $ui.apis.load_billoutpreprocess_id,
		fieldValue: 'billoutpreprocess_id',
		fieldValueMap: 'billoutpreprocess_id',
		fieldDisplay: 'billoutpreprocess_name',
		fields: [
			{mapping: 'billoutpreprocess_id', text: 'billoutpreprocess_id'},
			{mapping: 'billoutpreprocess_name', text: 'billoutpreprocess_name'},
		],
		OnDataLoading: (criteria) => {
						
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({billoutpreprocess_id:'--NULL--', billoutpreprocess_name:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {				
			}
		}
	})				
				

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

	//button state

}

export function OnSizeRecalculated(width, height) {
}

export function getForm() {
	return form
}


export function open(data, rowid, viewmode=true, fn_callback) {

	var pOpt = form.getDefaultPrompt(false)
	var fn_dataopening = async (options) => {
		options.criteria[form.primary.mapping] = data[form.primary.mapping]
	}

	var fn_dataopened = async (result, options) => {
		var record = result.record;
		updatefilebox(record);

		/*
		if (result.record.pph_taxtype_id==null) { result.record.pph_taxtype_id='--NULL--'; result.record.pph_taxtype_name='NONE'; }
		if (result.record.mediaorder_id==null) { result.record.mediaorder_id='--NULL--'; result.record.mediaorder_descr='NONE'; }
		if (result.record.mediaorderitem_id==null) { result.record.mediaorderitem_id='--NULL--'; result.record.mediaorderitem_descr='NONE'; }
		if (result.record.projbudgettask_id==null) { result.record.projbudgettask_id='--NULL--'; result.record.projbudgettask_name='NONE'; }
		if (result.record.billoutpreprocess_id==null) { result.record.billoutpreprocess_id='--NULL--'; result.record.billoutpreprocess_name='NONE'; }

		*/
		for (var objid in obj) {
			let o = obj[objid]
			if (o.isCombo() && !o.isRequired()) {
				var value =  result.record[o.getFieldValueName()];
				if (value==null ) {
					record[o.getFieldValueName()] = pOpt.value;
					record[o.getFieldDisplayName()] = pOpt.text;
				}
			}
		}
  		updaterecordstatus(record)

		form.SuspendEvent(true);
		form
			.fill(record)
			.setValue(obj.cbo_pph_taxtype_id, record.pph_taxtype_id, record.pph_taxtype_name)
			.setValue(obj.cbo_mediaorder_id, record.mediaorder_id, record.mediaorder_descr)
			.setValue(obj.cbo_mediaorderitem_id, record.mediaorderitem_id, record.mediaorderitem_descr)
			.setValue(obj.cbo_projbudget_id, record.projbudget_id, record.projbudget_name)
			.setValue(obj.cbo_projbudgettask_id, record.projbudgettask_id, record.projbudgettask_name)
			.setValue(obj.cbo_billoutpreprocess_id, record.billoutpreprocess_id, record.billoutpreprocess_name)
			.setViewMode(viewmode)
			.lock(false)
			.rowid = rowid


		/* tambahkan event atau behaviour saat form dibuka
		   apabila ada rutin mengubah form dan tidak mau dijalankan pada saat opening,
		   cek dengan form.isEventSuspended()
		*/   



		/* commit form */
		form.commit()
		form.SuspendEvent(false); 
		updatebuttonstate(record)

		// tampilkan form untuk data editor
		fn_callback()
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
		data.actual_duration = 0
		data.mediaorderitem_validr = 0
		data.mediaorderitem_ppnidr = 0

		data.pph_taxtype_id = '--NULL--'
		data.pph_taxtype_name = 'NONE'
		data.mediaorder_id = '--NULL--'
		data.mediaorder_descr = 'NONE'
		data.mediaorderitem_id = '--NULL--'
		data.mediaorderitem_descr = 'NONE'
		data.projbudget_id = '0'
		data.projbudget_name = '-- PILIH --'
		data.projbudgettask_id = '--NULL--'
		data.projbudgettask_name = 'NONE'
		data.billoutpreprocess_id = '--NULL--'
		data.billoutpreprocess_name = 'NONE'









		options.OnCanceled = () => {
			$ui.getPages().show('pnl_list')
		}



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


function updatefilebox(record) {
	// apabila ada keperluan untuk menampilkan data dari object storage

}

function updaterecordstatus(record) {
	// apabila ada keperluan untuk update status record di sini

}

function updatebuttonstate(record) {
	// apabila ada keperluan untuk update state action button di sini
	
}

function updategridstate(record) {
	// apabila ada keperluan untuk update state grid list di sini
	
}

function form_viewmodechanged(viewmode) {
	var OnViewModeChangedEvent = new CustomEvent('OnViewModeChanged', {detail: {}})
	$ui.triggerevent(OnViewModeChangedEvent, {
		viewmode: viewmode
	})
}

function form_idsetup(options) {
	var objid = obj.txt_medialogproofitem_id
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
	// options.skipmappingresponse = ['pph_taxtype_id', 'mediaorder_id', 'mediaorderitem_id', 'projbudgettask_id', 'billoutpreprocess_id', ];
	options.skipmappingresponse = [];
	for (var objid in obj) {
		var o = obj[objid]
		if (o.isCombo() && !o.isRequired()) {
			var id = o.getFieldValueName()
			options.skipmappingresponse.push(id)
			console.log(id)
		}
	}

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
	/*
	form.setValue(obj.cbo_pph_taxtype_id, result.dataresponse.pph_taxtype_name!=='--NULL--' ? result.dataresponse.pph_taxtype_id : '--NULL--', result.dataresponse.pph_taxtype_name!=='--NULL--'?result.dataresponse.pph_taxtype_name:'NONE')
	form.setValue(obj.cbo_mediaorder_id, result.dataresponse.mediaorder_descr!=='--NULL--' ? result.dataresponse.mediaorder_id : '--NULL--', result.dataresponse.mediaorder_descr!=='--NULL--'?result.dataresponse.mediaorder_descr:'NONE')
	form.setValue(obj.cbo_mediaorderitem_id, result.dataresponse.mediaorderitem_descr!=='--NULL--' ? result.dataresponse.mediaorderitem_id : '--NULL--', result.dataresponse.mediaorderitem_descr!=='--NULL--'?result.dataresponse.mediaorderitem_descr:'NONE')
	form.setValue(obj.cbo_projbudgettask_id, result.dataresponse.projbudgettask_name!=='--NULL--' ? result.dataresponse.projbudgettask_id : '--NULL--', result.dataresponse.projbudgettask_name!=='--NULL--'?result.dataresponse.projbudgettask_name:'NONE')
	form.setValue(obj.cbo_billoutpreprocess_id, result.dataresponse.billoutpreprocess_name!=='--NULL--' ? result.dataresponse.billoutpreprocess_id : '--NULL--', result.dataresponse.billoutpreprocess_name!=='--NULL--'?result.dataresponse.billoutpreprocess_name:'NONE')

	*/

	var pOpt = form.getDefaultPrompt(false)
	for (var objid in obj) {
		var o = obj[objid]
		if (o.isCombo() && !o.isRequired()) {
			var value =  result.dataresponse[o.getFieldValueName()];
			var text = result.dataresponse[o.getFieldDisplayName()];
			if (value==null ) {
				value = pOpt.value;
				text = pOpt.text;
			}
			form.setValue(o, value, text);
		}
	}
	form.rowid = $ui.getPages().ITEMS['pnl_list'].handler.updategrid(data, form.rowid)
}



async function form_deleting(data) {
}

async function form_deleted(result, options) {
	$ui.getPages().show('pnl_list')
	$ui.getPages().ITEMS['pnl_list'].handler.removerow(form.rowid)

}




