let editor, form, obj, opt;

const btn_generate = $('#pnl_edit-btn_generate')


export function init(ed) {
	editor = ed;
	form = editor.form;
	obj = editor.obj;
	opt = editor.opt;


	//btn_generate.linkbutton('disable');
	
}

export async function do_action_click(param) {
	console.log('generate');
	$ui.getPages().ITEMS['pnl_edit'].handler.detil_open('pnl_editbarcode');
	$('#pnl_editbarcode .fgta-page-title').html('Scan this Barcode');


	if ($('#pnl_editbarcode-barcode').length === 0) {
		$('#pnl_editbarcode').append('<svg id="pnl_editbarcode-barcode"></svg>');
	}


	var param = {
		topup_id: '12345678123456781234567812345678',
		storeId: '2222',
		cashierId: '3333',
		value: form.getValue(obj.txt_allotopup_validr)
	}

	var result = await getBarcode(param);

	if (result.success) {
		var barcode = result.barcode;
		var referenceNo = result.referenceNo;
		JsBarcode('#pnl_editbarcode-barcode', barcode, {format:'CODE128'});
		var cekstatus = setInterval(async ()=>{
			console.log('cek status');
			param.barcode = barcode;
			param.referenceNo = referenceNo;
			var rs = await getStatus(param);
			if (rs.status.topUpStatus=="00") {
				clearInterval(cekstatus);
				statusSuccess();
			} else if (rs.status.topUpStatus=='04') {
				clearInterval(cekstatus);
				statusFail();
			}
		}, 1000);
	}


}	

function statusSuccess() {
	$('#pnl_editbarcode-barcode').remove();
	$('#pnl_editbarcode').append('<div class="topup-success">Top Up Berhasil</div>');
	setTimeout(()=>{
		$ui.getPages().show('pnl_edit', ()=>{})
	}, 2000);
}

function statusFail() {
	$('#pnl_editbarcode-barcode').remove();
	$('#pnl_editbarcode').append('<div class="topup-fail">Top Up Gagal</div>');
	setTimeout(()=>{
		$ui.getPages().show('pnl_edit', ()=>{})
	}, 2000);
}


async function getStatus(param) {
	try {
		var apiurl = `${global.modulefullname}/xtion-barcodestatus`
		var args = {param: param}
		try {
			let result = await $ui.apicall(apiurl, args);
			return result;
		} catch (err) {
			console.log(err)
		}
	} catch (err) {
		console.error(err);
	}
}



async function getBarcode(param) {

	try {
		
		var apiurl = `${global.modulefullname}/xtion-barcodegenerate`
		var args = {param: param}
		try {
			let result = await $ui.apicall(apiurl, args);
			return result;
		} catch (err) {
			console.log(err)
		}
	} catch (err) {
		console.error(err);
	}
}
	
