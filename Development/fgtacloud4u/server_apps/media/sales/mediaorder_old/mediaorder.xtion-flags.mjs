export async function Flags(xtionname, id, fn_callback) {
	// console.log(corpbudget_id + 'commit');
	var apiurl = `${global.modulefullname}/${xtionname}`
	var args = {
		id: id
	}

	console.log(apiurl, args);
	$ui.mask('wait...')
	try {

		$ui.forceclosemask = true;
		var result = await $ui.apicall(apiurl, args);
		console.log('result', result);
		if (typeof fn_callback == 'function') {
			fn_callback(null, true);
		}

		$ui.unmask();
	} catch (err) {
		$ui.unmask();
		console.log(err)
		$ui.ShowMessage("[ERROR] " + err.errormessage);
	}

}