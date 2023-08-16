import { fgta4slideselect } from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'
import { fgta4ParallelProcess } from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4parallel.mjs'


var this_page_id;


const cbo_search_dept = $('#pnl_list-cbo_search_dept');
const obj_pastebox = $('#pnl_edituploads-obj_pastebox');
const obj_errormessage = $('#pnl_edituploads-errormessage');
const btn_downloadtemplate = $('#pnl_edituploads-downloadtemplate');
const pnl_result = $('#pnl_edituploads-result');

export async function init(opt) {
	this_page_id = opt.id
	
	btn_downloadtemplate.linkbutton({
		onClick: () => { btn_downloadtemplate_click() }
	})

	obj_pastebox[0].addEventListener('paste', (e) => {
		pnl_result.html('');

		var dept_id = cbo_search_dept.combo('getValue');
		if (dept_id=='0') {
			$ui.ShowMessage('[WARNING]Pilih dulu allokasi departemen sales', {
				'Ok': () => {
					obj_pastebox.html('');
				} 
			});
			return;
		}


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



	cbo_search_dept.name = 'pnl_list-cbo_search_dept'	
	new fgta4slideselect(cbo_search_dept, {
		title: 'Pilih Department',
		returnpage: this_page_id,
		api: $ui.apis.load_dept_id,

		fieldValue: 'dept_id',
		fieldValueMap: 'dept_id',
		fieldDisplay: 'dept_name',
		fields: [
			{ mapping: 'dept_name', text: 'Departemen' },
		],
		OnDataLoading: (criteria) => {
			// console.log('loading...');
		},
		OnDataLoaded: (result, options) => {
			// if (global.setup.empluser_allowviewalldept=='1') {
			// 	result.records.unshift({ dept_id: 'ALL', dept_name: 'ALL' });
			// }
		},
		OnSelected: (value, display, record, options) => {
			// console.log(record);
			options.flashhighlight = false
			// btn_load_click();
		},
		OnCreated: () => {
			cbo_search_dept.combo('setValue', '0');
			cbo_search_dept.combo('setText', '-- PILIH --');
			// parallelProcess.setFinished('cbo_search_dept_created');
		}
	});





	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show('pnl_list')
		}
	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
		}
	})

	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	})	
}


export function OnSizeRecalculated(width, height) {
}



export function OpenDetil(data) {
}



function btn_downloadtemplate_click() {
	$ui.download('media/sales/mediaorder/downloadtemplate', {options:{}})
}


async function obj_pastebox_paste(pastedData, fn_finish) {
	const parser = new NumberParser('en-US');
	var colspattern = [
		'TglTerimaMO', 
		'TglMediaOrder', 
		'MediaOrderId', 
		'MediaOrderRef', 
		'TrafficId', 
		'Status', 
		'Direct', 
		'Bundling', 
		'TypeMediaOrder', 
		'MediaOrderDescr', 
		'AgencyId', 
		'AgencyName', 
		'AdvertiserId', 
		'AdvertiserName', 
		'BrandId', 
		'BrandName', 
		'Value', 
		'JumlahSpot'
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
					TglTerimaMO: cells[0],
					TglMediaOrder: cells[1],
					MediaOrderId: cells[2],
					MediaOrderRef: cells[3],
					TrafficId: cells[4],
					Status: cells[5],
					Direct: cells[6],
					Bundling: cells[7],
					TypeMediaOrder: cells[8],
					MediaOrderDescr: cells[9],
					AgencyId: cells[10],
					AgencyName: cells[11],
					AdvertiserId: cells[12],
					AdvertiserName: cells[13],
					BrandId: cells[14],
					BrandName: cells[15],
					Value: parser.parse(cells[16]),
					JumlahSpot: parser.parse(cells[17]),
				}

				dataupload.push(celldata);
			}

			i++;
		}


		$ui.mask('wait...')
		try {
			var dept_id = cbo_search_dept.combo('getValue');

			var apiurl = `${global.modulefullname}/xtion-upload`
			var args = {
				dept_id: dept_id,
				data: dataupload
			}
	
			$ui.forceclosemask = true;
			var result = await $ui.apicall(apiurl, args);

			if (result.success==true) {
				console.log('upload ok');
			} else {
				
				for (var errline of result.errors) {
					pnl_result.append('<div>' + errline + '</div>');
				}
				throw new Error(result.errormessage);

			}
	
			$ui.unmask();
		} catch (err) {
			$ui.unmask();
			console.log(err)
			if (err.errormessage !== undefined) {
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

