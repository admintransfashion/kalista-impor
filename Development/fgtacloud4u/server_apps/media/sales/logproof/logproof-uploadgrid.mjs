var this_page_id;
var this_page_options;

const tbl_list = $('#pnl_edituploadgrid-tbl_list');
const txt_title = $('#pnl_edituploadgrid-title');
const pnl_control = $('.edituploadgrid-control');
const btn_removechecked  = $('#pnl_edituploadgrid-removechecked');
const btn_addrow = $('#pnl_edituploadgrid-addrow');
const obj_pastebox = $('#pnl_edituploadgrid-obj_pastebox');
const obj_errormessage = $('#pnl_edituploadgrid-errormessage');
const btn_downloadtemplate = $('#pnl_edituploadgrid-downloadtemplate');
const btn_generate = $('#pnl_edituploadgrid-generate');


let grd_list = {};
let header_data = {};
let last_scrolltop = 0;

export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;
	
	
	grd_list = new global.fgta4grid(tbl_list, {
		OnRowFormatting: (tr) => { grd_list_rowformatting(tr) },
		OnRowClick: (tr, ev) => { grd_list_rowclick(tr, ev) },
		OnCellClick: (td, ev) => { grd_list_cellclick(td, ev) },
		OnCellRender: (td) => { grd_list_cellrender(td) },
		OnRowRender: (tr) => { grd_list_rowrender(tr) }
	});	

	btn_removechecked.linkbutton({
		onClick: () => { btn_removechecked_click() }
	});

	btn_addrow.linkbutton({
		onClick: () => { btn_addrow_click() }
	});

	btn_downloadtemplate.linkbutton({
		onClick: () => { btn_downloadtemplate_click() }
	})

	btn_generate.linkbutton({
		onClick: () => { btn_generate_click() }
	})


	obj_pastebox[0].addEventListener('paste', (e) => {
		var clipboardData = e.clipboardData || window.clipboardData;
		var pastedData = clipboardData.getData('Text');
		$ui.ShowMessage("[QUESTION]Apakah anda yakin akan <b>upload</b> data ?", {
			"OK": async () => {
				obj_pastebox.hide();
				obj_pastebox.html('');
				obj_errormessage.html('');
				obj_pastebox_paste(pastedData, (err)=>{
					obj_pastebox.show();
				});
			},
			"Cancel": () => {
			}
		})

	});


	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show('pnl_edit')
		}
	});

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
		}
	});

	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	});	

	document.addEventListener('OnViewModeChanged', (ev) => {
		if (ev.detail.viewmode===true) {
			pnl_control.hide()
		} else {
			pnl_control.show()
		}
	});

	document.addEventListener('scroll', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if($(window).scrollTop() + $(window).height() == $(document).height()) {
				grd_list.nextpageload();
			}			
		}
	});			
}


export function OnSizeRecalculated(width, height) {
}



export function createnew(data, options) {
	// pada saat membuat data baru di header
	obj_errormessage.html('');

	grd_list.clear();
	txt_title.html(data.medialogproof_date)
	header_data = data;
}

export function OpenDetil(data) {
	// saat di klik di edit utama, pada detil information
	obj_errormessage.html('');

	grd_list.clear();
	txt_title.html(data.medialogproof_date)
	header_data = data;

	var fn_listloading = async (options) => {
		options.api = `${global.modulefullname}/upload-list`
		options.criteria['id'] = data.medialogproof_id
	}
	var fn_listloaded = async (result, options) => {
		// console.log(result)





		var detilform = $ui.getPages().ITEMS['pnl_edituploadform'].handler.getForm()

		if (detilform.AllowAddRecord) {
			btn_addrow.show()
		} else {
			btn_addrow.hide()
		}

		if (detilform.AllowRemoveRecord) {
			btn_removechecked.show()
		} else {
			btn_removechecked.hide()
		}

	}

	grd_list.listload(fn_listloading, fn_listloaded)	
}

export function scrolllast() {
	$(window).scrollTop(last_scrolltop)
}

export function updategrid(data, trid) {
	if (trid==null) {
		grd_list.fill([data])
		trid = grd_list.getLastId()
		
	} else {
		grd_list.update(trid, data)
	}

	return trid
}

export function removerow(trid) {
	grd_list.removerow(trid)
}

export function getGrid() {
	return grd_list
}


function grd_list_rowformatting(tr) {

}

function grd_list_rowclick(tr, ev) {
	// console.log(tr)
	var trid = tr.getAttribute('id')
	var dataid = tr.getAttribute('dataid')
	var record = grd_list.DATA[dataid]
	// console.log(record)

	last_scrolltop = $(window).scrollTop()
	$ui.getPages().show('pnl_edituploadform', () => {
		$ui.getPages().ITEMS['pnl_edituploadform'].handler.open(record, trid, header_data)
	})	
}

function grd_list_cellclick(td, ev) {

}

function grd_list_cellrender(td) {

}

function grd_list_rowrender(tr) {

}


function btn_removechecked_click() {
	$ui.ShowMessage("[QUESTION]Apakah anda akan menghapus baris terpilih ?" , {
		yes: () => {
			grd_list.removechecked({
				OnRemoving : async (options) => {
					var apiurl = `${global.modulefullname}/upload-delete`
					var args = {data: options.data, options: {}}
					try {
						let result = await $ui.apicall(apiurl, args)
					} catch (err) {
						console.log(err)
					}
				}
			})
		},
		no: () => {}
	})
}


function btn_addrow_click() {
	$ui.getPages().show('pnl_edituploadform', ()=>{
		$ui.getPages().ITEMS['pnl_edituploadform'].handler.createnew(header_data)
	})	
}




async function obj_pastebox_paste(pastedData, fn_finish) {
	const parser = new NumberParser('en-US');

	var colspattern = [
		'Date', 
		'SlotTimeStart', 
		'SlotTimeEnd', 
		'SlotDuration', 
		'SlotDescr', 
		'SlotCode', 
		'ActualTimeStart', 
		'ActualTimeEnd', 
		'ActualDuration', 
		'SpotId', 
		'MediaOrderId', 
		'MediaOrderRef', 
		'TypeMediaOrder', 
		'MediaOrderDescr', 
		'AgencyCode', 
		'AgencyName', 
		'AdvertiserCode', 
		'AdvertiserName', 
		'BrandCode', 
		'BrandName', 
		'ProgrammeCode', 
		'ProgrammeName', 
		'EpisodeCode', 
		'EpisodeName', 
		'Value'
	];

	var rows = pastedData.split("\n");
	try {

		var dataupload = [];
		var i = 0;
		for (var row of rows) {
			if (row === "") continue;
	
			var cells = row.split("\t");
			if (i==0) {
				var cellpattern = [];
				for (var cell of cells) {
					cellpattern.push(cell.trim())
				}

				var headpatt = cellpattern.join(' $ ').trim().toUpperCase();
				var colspatt = colspattern.join(' $ ').trim().toUpperCase();

	
				// if (true) {
				if (headpatt!=colspatt) {
					// console.log(headpatt);
					// console.log(colspatt);
					throw new Error('Format data tidak sesuai');
				}
			} else {

				var celldata = {
					Date: cells[0],
					SlotTimeStart: cells[1],
					SlotTimeEnd: cells[2],
					SlotDuration: cells[3],
					SlotDescr: cells[4],
					SlotCode: cells[5],
					ActualTimeStart: cells[6],
					ActualTimeEnd: cells[7],
					ActualDuration: cells[8],
					SpotId: cells[9],
					MediaOrderId: cells[10],
					MediaOrderRef: cells[11],
					TypeMediaOrder: cells[12],
					MediaOrderDescr: cells[13],
					AgencyCode: cells[14],
					AgencyName: cells[15],
					AdvertiserCode: cells[16],
					AdvertiserName: cells[17],
					BrandCode: cells[18],
					BrandName: cells[19],
					ProgrammeCode: cells[20],
					ProgrammeName: cells[21],
					EpisodeCode: cells[22],
					EpisodeName: cells[23],
					Value:  parser.parse(cells[24])
				}

				dataupload.push(celldata);
			}

			i++;
		}


		$ui.mask('wait...')
		try {
	
			var apiurl = `${global.modulefullname}/xtion-upload`
			var args = {
				id: header_data.medialogproof_id,
				data: dataupload
			}
	
			$ui.forceclosemask = true;
			var result = await $ui.apicall(apiurl, args);

			if (result.success==true) {
				OpenDetil(header_data);
			}
	
			$ui.unmask();
		} catch (err) {
			$ui.unmask();
			console.log(err)
			if (err.errormessag !== undefined) {
				// error dari AJAX
				$ui.ShowMessage("[WARNING] " + err.errormessage);
			} else {
				// error yang lain
				$ui.ShowMessage("[WARNING] " + err.message);
			}
	
		}		
	


	} catch (err) {
		console.error(err);
		var errmessage = err.message != null ? err.message : err;
		obj_errormessage.html(errmessage);
	} finally {
		if (typeof fn_finish === 'function') {
			fn_finish();
		}
	}

}

function btn_downloadtemplate_click() {
	$ui.download('media/sales/logproof/downloadtemplate', {options:{}})
}





class NumberParser {
	constructor(locale) {
	  const format = new Intl.NumberFormat(locale);
	  const parts = format.formatToParts(12345.6);
	  const numerals = Array.from({ length: 10 }).map((_, i) => format.format(i));
	  const index = new Map(numerals.map((d, i) => [d, i]));
	  this._group = new RegExp(`[${parts.find(d => d.type === "group").value}]`, "g");
	  this._decimal = new RegExp(`[${parts.find(d => d.type === "decimal").value}]`);
	  this._numeral = new RegExp(`[${numerals.join("")}]`, "g");
	  this._index = d => index.get(d);
	}
	parse(string) {
	  return (string = string.trim()
		.replace(this._group, "")
		.replace(this._decimal, ".")
		.replace(this._numeral, this._index)) ? +string : NaN;
	}
  }


  async function btn_generate_click() {
	var apiurl = `${global.modulefullname}/xtion-generate`
	var args = {
		id: header_data.medialogproof_id
	}

	try {
		let result = await $ui.apicall(apiurl, args)




	} catch (err) {
		console.log(err)
		$ui.ShowMessage('[ERROR]' + err.errormessage);
	}
  }