var this_page_id;
var this_page_options;

import * as fgta4longtask from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4longtask.mjs'

// const btn_syncstart = $('#pnl_main-task .task-start');
// const btn_synccancel = $('#pnl_main-task .task-cancel')


const longtask = fgta4longtask.init('#pnl_main-task', {name: 'progress-syncregister'});


export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;

	longtask.Start = () => { longtask_starting(); }
	longtask.RequestCancel = () => { longtask_canceling(); }

}



async function longtask_starting() {
	console.log('starting task');


	longtask.MonitorProgress();
}


async function longtask_canceling() {
	console.log('canceling task');



	// jika berhasil cancel
	longtask.Cancel();
}





// async function btn_synctb_click(btn) {

// 	var opt = btn.linkbutton('options');
// 	var oritext = opt.text;

// 	btn.blur(); 
// 	btn.linkbutton('disable');
// 	btn.linkbutton({text:'Wait...'});

// 	console.log('btn_synctb_click');

// 	try {
// 		var param = {};

// 		// var mask = $ui.mask('wait...');
// 		var apiurl = `${global.modulefullname}/syncregister`
// 		var args = {param: param}
// 		try {
// 			let result = await $ui.apicall(apiurl, args);
// 			var progress_file = result.progress_file;
// 			// console.log(progress_file);

// 			var cekProgress = setInterval(async ()=>{
// 				var apiurl = `fgta/framework/progress/cek`
// 				var args = { param : {progress_file: progress_file} }
// 				let result = await $ui.apicall(apiurl, args);
// 				console.log(result);
// 			}, 1000);


// 		} catch (err) {
// 			console.log(err)
// 		} finally {
// 			// $ui.unmask(mask);
// 		}
// 	} catch (err) {
// 		console.error(err);
// 	}
// }

