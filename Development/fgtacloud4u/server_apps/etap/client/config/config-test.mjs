var this_page_id;
var this_page_options;


import * as drivers from './xtion-drivers.mjs';

const btn_test_mysql = $('#btn_test_mysql');
const btn_test_sqlsrv = $('#btn_test_sqlsrv');
const btn_udpatedriver = $('#btn_udpatedriver');



export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;


	btn_test_mysql.linkbutton({
		onClick: () => { btn_test_mysql_click(); }
	});

	btn_test_sqlsrv.linkbutton({
		onClick: () => { btn_test_sqlsrv_click(); }
	});

	btn_udpatedriver.linkbutton({
		onClick: () => { btn_udpatedriver_click(); }
	});	
}


async function btn_udpatedriver_click() {
	drivers.update('mysql');
}


function btn_test_mysql_click() {
	console.log('Test Konek ke local MySQL yaa');

	// var machine_id = window.parent.getMachineId();

	var param = { test: '1234' };
	window.parent.LocalExecute(param, async ()=>{
		var mysql = window.parent.LocalRequire('mysql');
		var con = mysql.createConnection({
			host: "localhost",
			user: "root",
			password: ""
		});

		con.connect(function(err) {
			if (err) {
				console.error(err);
			} else {
				console.log("Connected!");
				console.log(param);
			}
		});

	})

	// window.parent.UpdateDriver('testdrive');
	// console.log(window.parent.getLocalAPI());

	// var mysql = window.parent.LocalRequire('mysql');
	// var con = mysql.createConnection({
	// 	host: "localhost",
	// 	user: "root",
	// 	password: ""
	//   });

	//   con.connect(function(err) {
	// 	if (err) {
	// 		console.error(err);
	// 	} else {
	// 		console.log("Connected!");
	// 	}
	//   });
}


function btn_test_sqlsrv_click() {
	console.log('Test Konek ke local SQL Server');

}