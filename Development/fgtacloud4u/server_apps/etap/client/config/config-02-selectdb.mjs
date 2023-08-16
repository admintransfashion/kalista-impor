var this_page_id;
var this_page_options;

import {fgta4slideselect} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'

const pnl_form = $('#pnl_selectdb-form')
const obj = {
	cbo_dbtype_id: $('#pnl_selectdb-cbo_dbtype_id'),
	txt_dbhost: $('#pnl_selectdb-txt_dbhost'),
	txt_dbport: $('#pnl_selectdb-txt_dbport'),
	txt_dbname: $('#pnl_selectdb-txt_dbname'),
	txt_dbschema : $('#pnl_selectdb-txt_dbschema'),
	txt_dbuser: $('#pnl_selectdb-txt_dbuser'),
	txt_dbpassword: $('#pnl_selectdb-txt_dbpassword')	
}

const btn_next = $('#pnl_selectdb-btn_next');
const drivers = window.parent.LocalRequire('./drivers.js');

let form = {}
var currentconfig = {};

export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;

	form = new global.fgta4form(pnl_form, {
		objects : obj,
	});

	obj.cbo_dbtype_id.name = "cbo_dbtype_id";
	new fgta4slideselect(obj.cbo_dbtype_id, {
		title: 'Pilih Database Engine',
		returnpage: this_page_id,
		api: 'ent/general/config/list',
		fieldValue: 'config_id',
		fieldValueMap: 'config_id',
		fieldDisplay: 'config_name',
		fields: [
			{mapping: 'config_id', text: 'config_id'},
			{mapping: 'config_name', text: 'config_name'},
		],
		OnDataLoading: (criteria) => {},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record) => {}
	});
	
	

	btn_next.linkbutton({
		onClick: () => { btn_next_click(); }
	})


	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show('pnl_selectsite', (pnl)=>{
				$ui.setPanelShowingInfo(pnl.id);
			})
		};			

	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
		};			
	})


	
	// $ui.setEditStyle(obj);
}



export async function ReadCurrentDbConfiguration(siteconfig, fn_selected) {
	// console.log(siteconfig)
	obj.cbo_dbtype_id.combo('setValue', siteconfig.config_id);
	obj.cbo_dbtype_id.combo('setText', siteconfig.config_name);

	

	try {
		
		$ui.mask('read current setting ...');
		currentconfig = await global.cachedb.setup_get_setting();
		currentconfig.tenant_id = siteconfig.site_id;
		currentconfig.tenant_name = siteconfig.site_name;
		currentconfig.dbtype = siteconfig.dbtype;

		// current config
		obj.txt_dbhost.textbox('setValue', currentconfig.server.host);
		obj.txt_dbport.textbox('setValue', currentconfig.server.port);
		obj.txt_dbname.textbox('setValue', currentconfig.server.database);
		obj.txt_dbschema.textbox('setValue', currentconfig.server.schema)
		obj.txt_dbuser.textbox('setValue', currentconfig.server.user);
		// obj.txt_dbpassword.textbox('getValue', currentconfig.server.password);
		obj.txt_dbpassword.passwordbox('setValue', "rahasia");

		await global.cachedb.setup_update_setting(currentconfig);

		form.setViewMode(false);
		if (typeof fn_selected === 'function') {
			fn_selected();
		}
	} catch (err) {
		$ui.unmask();
		$ui.ShowMessage('[ERROR]'+err.message);
		console.error(err)
	} finally {
		setTimeout(()=>{
			$ui.unmask();
		}, 1000);
	}
}


function btn_next_click() {
	// test koneksi dulu
	;(async () => {
		try {
			$ui.mask('connecting ...')
			currentconfig.server = {
				host: obj.txt_dbhost.textbox('getValue').trim(),
				port: obj.txt_dbport.textbox('getValue').trim(),
				database: obj.txt_dbname.textbox('getValue').trim(),
				schema: obj.txt_dbschema.textbox('getValue').trim(),
				user: obj.txt_dbuser.textbox('getValue').trim(),
				password: obj.txt_dbpassword.textbox('getValue')
			}

			try {
				if (currentconfig.server.host=='') {
					throw 'host belum diisi';
				} else if (currentconfig.server.database=='') {
					throw 'database belum diisi';
				} else if (currentconfig.server.user=='') {
					throw 'user belum diisi';
				}
			} catch (err) {
				await new Promise((resolve) => {
					$ui.ShowMessage(`[WARNING]${err}`, {
						'Ok': () => {
							resolve()
						}
					})
				});
				return;
			}


			var drivername = currentconfig.dbtype;
			var driverpath = drivers.getdriverpath(drivername);

			var drv = window.parent.LocalRequire(driverpath)(currentconfig);
			try {
				await drv.connect();
				await global.cachedb.setup_update_setting(currentconfig);
				$ui.unmask();
				$ui.ShowMessage('[INFO]Connected', {
					'Ok': () => {
						// simpan ke configurasi
						var selectdb_panel_name = 'pnl_selectmodel';
						$ui.getPages().show(selectdb_panel_name, async (pnl) => {
							obj.txt_dbpassword.textbox('setValue', '');
							$ui.setPanelShowingInfo(pnl.id);
							var pnl_selectmodel = $ui.getPages().ITEMS[selectdb_panel_name];
							await pnl_selectmodel.handler.selected();

							for (var schemaname of ['pnl_schema1', 'pnl_schema2', 'pnl_schema3']) {
								var pnl_schema = $ui.getPages().ITEMS[schemaname];
								await pnl_schema.handler.Reset();
							}


						})							
					}
				})
			} catch (err) {
				var usingpassword = currentconfig.server.password=='' ? 'no' : 'yes';
				$ui.ShowMessage(`[ERROR]Cannot connect to database '${currentconfig.server.database}@${currentconfig.server.host}',<br>user ${currentconfig.server.user} using password: ${usingpassword}`, {
					'Ok': () => {
					}
				})
			} finally {
				await drv.close();
			}
			
		} catch (err) {
			$ui.unmask();
			console.error(err);
			$ui.ShowMessage('[ERROR]' + err.message);
		} finally {
			$ui.unmask();
		}
	})();


}