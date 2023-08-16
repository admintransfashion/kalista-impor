var this_page_id;
var this_page_options;


const pnl_form = $('#pnl_final-form');
const btn_test = $('#pnl_final-btn_test');
const btn_finish = $('#pnl_final-btn_finish')
const txt_result = $('#pnl_final-txt_result');
const box_finish = $('#pnl_final-box_finish');

const obj = {
	dt_datestart : $('#pnl_final-dt_datestart'),
	chk_view_response: $('#pnl_final-chk_view_response'),
	chk_view_data: $('#pnl_final-chk_view_data'),
}

const datasender = window.parent.LocalRequire('./datasender.js');


var this_back_panelname;

var currentconfig = {};
var form = {}




export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;

	form = new global.fgta4form(pnl_form, {
		objects : obj,
	});

	btn_test.linkbutton({onClick: () => { btn_test_click(); }})
	btn_finish.linkbutton({onClick: () => { btn_finish_click(); }})

	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show(this_back_panelname, (pnl)=>{
				$ui.setPanelShowingInfo(pnl.id);
			})
		};			

	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
		};			
	});


}




export function setFinalizedFor(param) {
	currentconfig = param.currconf;
	this_back_panelname = param.panelname;

	obj.dt_datestart.datebox('setValue', global.now());
	form.setViewMode(false);

	box_finish.hide();
}



async function btn_test_click() {
	var chk_response = obj.chk_view_response.checkbox('options');
	var chk_data = obj.chk_view_data.checkbox('options');
	
	try {

		$ui.mask('getting records form database, please wait...')

		// simpan setting
		var dt = obj.dt_datestart.datebox('getValue');
		var sqdate = window.to_sql_date(dt);

		currentconfig.lastfetchdate = sqdate;
		await global.cachedb.setup_update_setting(currentconfig);

		var lastdatesent;
		txt_result.html('');
		datasender.setNoSendingConsoleLog();
		datasender.setConfigCacheDb(global.cachedb);
		datasender.setConfig(currentconfig);
		datasender.run(undefined, (message, type)=>{
			// on sending
			if (type==='response') {
				if (chk_response.checked) txt_result.append(message + '<br>\r\n');	
			}

			if (type==='result' || type=='success' || type==='error') {
				txt_result.append(message + '<br>\r\n');	
			}

			if (type==='data') {
				lastdatesent = message.header.tx_date;
				if (chk_data.checked) txt_result.append(JSON.stringify(message)  + '<br>\r\n');
			}

			txt_result[0].scrollTop = txt_result[0].scrollHeight;

		}, (param, err)=> {
			if (err) {
				setTimeout(()=>{
					$ui.unmask();
					$ui.ShowMessage('[ERROR]'+err.message);
				}, 1000);
			} else {
				box_finish.show();
				setTimeout(()=>{
					$ui.unmask();
				}, 1000);
			}
		});
	} catch (err) {
		console.error(err);
		$ui.ShowMessage('[ERROR]' + err.message);
	}
}


function btn_finish_click() {
	$ui.ShowMessage('[QUESTION]Apakah anda sudah selesai setup dan mau menutup window ini?', {
		'Ok': () => {
			window.parent.MainWindowClose();
		},
		'Cancel' : () => {}
	})
}