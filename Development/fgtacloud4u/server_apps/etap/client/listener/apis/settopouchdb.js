'use strict'

start((err, result) => {
	console.log(result);
});


async function start(fn_callback) {
	setTimeout(()=>{
		fn_callback(null, "ini hasilnya");
	}, 1000);
}





