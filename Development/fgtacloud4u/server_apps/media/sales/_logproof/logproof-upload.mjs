const obj_pastebox = $('#pnl_upload-obj_pastebox')


const tbl_list = $('#pnl_upload-tbl_list');
const btn_upload = $('#pnl_upload-btn_upload');

let grd_list = {}

export async function init(opt) {


	grd_list = new global.fgta4grid(tbl_list, {
		// OnRowFormatting: (tr) => { grd_list_rowformatting(tr) },
		// OnRowClick: (tr, ev) => { grd_list_rowclick(tr, ev) },
		// OnCellClick: (td, ev) => { grd_list_cellclick(td, ev) },
		// OnCellRender: (td) => { grd_list_cellrender(td) },
		// OnRowRender: (tr) => { grd_list_rowrender(tr) }
	})


	obj_pastebox[0].addEventListener('paste', (e) => {
		var clipboardData = e.clipboardData || window.clipboardData;
		var pastedData = clipboardData.getData('Text');
		$ui.ShowMessage("[QUESTION]Apakah anda yakin akan <b>upload</b> data ?", {
			"OK": async () => {
				obj_pastebox.hide();
				obj_pastebox.html('');
				obj_pastebox_paste(pastedData, (err)=>{
					obj_pastebox.show();
				});
			},
			"Cancel": () => {
			}
		})

	});


	btn_upload.linkbutton({onClick: () => { btn_upload_click() }});
}




function obj_pastebox_paste(pastedData, fn_paste) {
	const colspattern = "No $ Agency $ Advertiser $ Brand $ Order# $ Order $ EndDate $ Aired $ NettInvoice $ TypePayment";
	const formatter = new Intl.NumberFormat('en-US');
	const parser = new NumberParser('en-US');


	try {
		var rows = pastedData.split("\n");
		var i = 0;
		var totalNettInvoice = 0;
		for (var row of rows) {
			if (row === "") continue;
	
			var cells = row.split("\t");
			if (i==0) {
				var headpatts = [];
				for (var cell of cells) {
					var c = cell.replace(/\s/g, '');
					console.log(`*${c}*`);
					headpatts.push(c);
				}
				var headpatt = headpatts.join(' $ ').trim();
				console.log(headpatt.toUpperCase())
				console.log(colspattern.toUpperCase());
				if (headpatt.toUpperCase()!=colspattern.toUpperCase()) {
					console.log(headpatt.toUpperCase());
					console.log(colspattern.toUpperCase());
					throw new Error('Format data tidak sesuai');
				}
			} else {


				var No = cells[0];
				var Agency = cells[1];
				var Advertiser = cells[2];
				var Brand = cells[3];
				var OrderName = cells[4];
				var OrderId = cells[5];
				var EndDate = cells[6];
				var Aired = cells[7];
				var NettInvoice = parser.parse(cells[8]);
				var TypePayment = cells[9];

				var nettValue = isNaN(NettInvoice) ? 0 : NettInvoice;
				totalNettInvoice += nettValue;
				if (cells.join('').replace(/\s/g, '')=='') continue;
				grd_list.fill([{
					No : No,
					Agency : Agency,
					Advertiser : Advertiser,
					Brand : Brand,
					OrderId : OrderId,
					OrderName : OrderName,
					EndDate : EndDate,
					Aired : Aired,
					NettInvoice : nettValue,
					TypePayment : TypePayment,
				}]);

			}

			i++;
		}


		var eTotal = $('#totalNettInvoice');
		eTotal.html(formatter.format(totalNettInvoice));

		if (typeof fn_paste === 'function') {
			fn_paste();
		}
	} catch (err) {
		console.log(err);
		if (typeof fn_paste === 'function') {
			fn_paste(err);
		}	
	}	

}


async function btn_upload_click() {
	var rows = grd_list.getdata();

	try {

		$ui.forceclosemask = true;
		$ui.mask('wait...')
		for (var i in rows) {
			var row = rows[i];
	
			var apiurl = `${global.modulefullname}/xtion-uploadlogproof`
			var args = {
				data: row
			}
		
			console.log(apiurl, args);
			try {
				var result = await $ui.apicall(apiurl, args);
			} catch (err) {
				throw err;
			}
		}

		$ui.unmask();
		$ui.getPages().show('pnl_list', ()=>{
			$ui.getPages().ITEMS['pnl_list'].handler.reload();
		})	


	} catch (err) {
		$ui.unmask();
		console.error(err);
		$ui.ShowMessage("[ERROR] " + err.errormessage);
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