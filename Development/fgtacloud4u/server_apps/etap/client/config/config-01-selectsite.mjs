var this_page_id;
var this_page_options;


const tbl_list = $('#pnl_selectsite-tbl_list')

let grd_list = {}

let last_scrolltop = 0

export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;

	grd_list = new global.fgta4grid(tbl_list, {
		OnRowClick: (tr, ev) => { grd_list_rowclick(tr, ev) },
	})
	// await loadsites();

	document.addEventListener('scroll', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if($(window).scrollTop() + $(window).height() == $(document).height()) {
				grd_list.nextpageload();
			}			
		}
	})	

	load_data();

}


async function grd_list_rowclick(tr, ev) {
	var dataid = tr.getAttribute('dataid')
	var record = grd_list.DATA[dataid]
	// console.log(record)


	var param = {
		drivername: record.dbtype,
	}
	

	if (param.drivername.substr(param.drivername.length - 5)=='-test') {
		show_nextpanel(record);
	} else {
		$ui.download(global.modulefullname + '/downloaddriver', param, async (res, err) => {
			if (err!=null) {
				$ui.ShowMessage('[ERROR]' + err.message);
				return;
			}
			try {
				var res = window.parent.UpdateDriver(param.drivername, res.data);
				show_nextpanel(record);
			} catch (err) {
				$ui.ShowMessage('[ERROR]'.err.message);
			}
		})
	}


}

function show_nextpanel(record) {
	var selectdb_panel_name = 'pnl_selectdb';
	var pnl_selectdb = $ui.getPages().ITEMS[selectdb_panel_name];
	pnl_selectdb.handler.ReadCurrentDbConfiguration(record, () => {
		$ui.getPages().show(selectdb_panel_name, (pnl)=>{
			$ui.setPanelShowingInfo(pnl.id);
			$('.namatoko').html(record.site_name);
		});
	});
}



function load_data() {
	grd_list.clear()
	var fn_listloading = async (options) => {
		options.api = `${global.modulefullname}/gettodayusersitesetup`
	}

	var fn_listloaded = async (result, options) => {
		// console.log(result)
	}



	grd_list.listload(fn_listloading, fn_listloaded)	
}

