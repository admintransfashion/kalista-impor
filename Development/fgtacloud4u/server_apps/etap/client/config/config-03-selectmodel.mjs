var this_page_id;
var this_page_options;


const btn_next = $('#pnl_selectmodel-btn_next');


var currentconfig = {};

export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;



	var btnselectors = document.getElementsByName('pnl_selectmodel-schema');
	for (var btn of btnselectors) {
		let schemaname = btn.getAttribute('schemaname');
		btn.addEventListener('click', (ev) => {
			var rdo = document.getElementById('pnl_selectmodel-' + schemaname);
			rdo.checked = true;
		});
	}

	btn_next.linkbutton({
		onClick: () => { btn_next_click(); }
	})


	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			$ui.getPages().show('pnl_selectdb', (pnl)=>{
				$ui.setPanelShowingInfo(pnl.id);
			})
		};			

	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
		};			
	})
}


export async function selected() {
	$ui.mask('update setting ...')

	try {
		currentconfig = await global.cachedb.setup_get_setting();
		var schemaname = currentconfig.schema.model;
		var rdo = document.querySelector(`input[type="radio"][schemaname="${schemaname}"]`);
		rdo.checked = true;
		$ui.unmask();
	} catch (err) {
		$ui.unmask();
		console.error(err)
	} finally {
		$ui.unmask();
	}	

}


function btn_next_click() {




	;(async () => {
		try {
			$ui.mask('connecting ...')
			var rdo = document.querySelector('input[type="radio"][name="pnl_selectmodel-schema"]:checked');
			if (rdo!==null) {
				var schemaname = rdo.value;
				var nextpnl = 'pnl_' + schemaname;
				var previous_schema_model = currentconfig.schema.model;

				var gonextconfig = async () => {
					currentconfig.schema.model = schemaname;

					await global.cachedb.setup_update_setting(currentconfig);
					$ui.unmask();
			
					var pnl_nextpnl = $ui.getPages().ITEMS[nextpnl];
					await pnl_nextpnl.handler.selecting(currentconfig);
					$ui.getPages().show(nextpnl, () => {
						pnl_nextpnl.handler.selected(schemaname==previous_schema_model ? true : false);
					})
				}
			

				if (schemaname!=previous_schema_model) {
					$ui.ShowMessage('[WARNING]Schema yang anda pilih berbeda dengan schema sebelumnya. Seluruh konfigurasi pada skema sebelumnya akan ditimpa. Apakah anda yakin?', {
						'Ok': async () => { await gonextconfig(); },
						'Cancel': () => {}
					});
				} else {
					await gonextconfig();
				}


			}

		} catch (err) {
			$ui.unmask();
			console.error(err);
		} finally {
			$ui.unmask();
		}
	})();

}