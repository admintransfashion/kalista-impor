import {fgta4grid } from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4grid.mjs'
import {fgta4form} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4form.mjs'
import {fgta4slideselect2} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect2.mjs'
import * as fgta4pages from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4pages.mjs'
import * as fgta4pageslider from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4pageslider.mjs'


import * as pSelectSite from './config-01-selectsite.mjs'
import * as pSelectDb from './config-02-selectdb.mjs'
import * as pSelectModel from './config-03-selectmodel.mjs'
import * as pSchema1 from './config-04a-schema1.mjs'
import * as pSchema2 from './config-04b-schema2.mjs'
import * as pSchema3 from './config-04c-schema3.mjs'
import * as pVerify from './config-05-verify.mjs'
import * as pFinal from './config-06-final.mjs'


import * as pTest from './config-test.mjs'


const pnl_selectsite = $('#pnl_selectsite')
const pnl_selectdb = $('#pnl_selectdb')
const pnl_selectmodel = $('#pnl_selectmodel')
const pnl_schema1 = $('#pnl_schema1');
const pnl_schema2 = $('#pnl_schema2');
const pnl_schema3 = $('#pnl_schema3');
const pnl_verify = $('#pnl_verify');
const pnl_final = $('#pnl_final');


const pnl_test = $('#pnl_test')
const obj_panelname = $('#obj_panelname');

const drivers = window.parent.LocalRequire('./drivers.js');

var pages = fgta4pages;
var slider = fgta4pageslider;


export const SIZE = {width:0, height:0}


export async function init(opt) {

	global.fgta4grid = fgta4grid
	global.fgta4form = fgta4form
	global.cachedb =  window.parent.getCacheDb();


	document.getElementsByTagName("body")[0].style.margin = '15px 5px 5px 25px'

	pages
	.setSlider(slider)
	.initPages([
		{panel: pnl_selectsite, handler: pSelectSite},
		{panel: pnl_selectdb, handler: pSelectDb},
		{panel: pnl_selectmodel, handler: pSelectModel},
		{panel: pnl_schema1, handler: pSchema1},
		{panel: pnl_schema2, handler: pSchema2},
		{panel: pnl_schema3, handler: pSchema3},
		{panel: pnl_verify, handler: pVerify},
		{panel: pnl_test, handler: pTest},
		{panel: pnl_final, handler: pFinal}
	], opt)

	$ui.setPages(pages)
	$ui.setPanelShowingInfo('pnl_selectsite');

	

}

export function setPanelShowingInfo(panelname) {
	obj_panelname.html(panelname)
}



export async function schema_gettablelist(currentconfig) {
	try {
		var drivername = currentconfig.dbtype;
		var driverpath = drivers.getdriverpath(drivername);
	
		var drv = window.parent.LocalRequire(driverpath)(currentconfig);
		try {
			await drv.connect();
			
			// query dapatkan table;
			var tables = await drv.setup_get_tables();
			return tables;

		} 	catch (err) {
			console.error(err);
		} finally {
			await drv.close();
		}

	} catch (err) {
		console.error(err);
	}
}


export async function schema_getfields(currentconfig, tablename) {
	try {
		var drivername = currentconfig.dbtype;
		var driverpath = drivers.getdriverpath(drivername);
	
		var drv = window.parent.LocalRequire(driverpath)(currentconfig);
		try {
			await drv.connect();
			
			// query dapatkan table;
			var fields = await drv.setup_get_fields(tablename);
			return fields;

		} 	catch (err) {
			console.error(err);
		} finally {
			await drv.close();
		}

	} catch (err) {
		console.error(err);
	}
}


export function setTableSelector(fn_get_currentconfig, tablecbo, tabledata, obj, this_page_id, fn_tablename_selected) {


	for (var tableid in tabledata) { 
		tablecbo.push(tabledata[tableid].tablecboname) 
	}

	for (let tableid in tabledata) {
		let tablecboname = tabledata[tableid].tablecboname;
		obj[tablecboname].name = obj[tablecboname].attr('id');
		obj[tablecboname].tableid = tableid;
		new fgta4slideselect2(obj[tablecboname], {
			title: 'Pilih ' + tabledata[tableid].tableinfoel.attr('title'),
			returnpage: this_page_id,
			fieldValue: 'table_id',
			fieldValueMap: 'table_id',
			fieldDisplay: 'table_name',
			fields: [
				{mapping: 'table_id', text: 'table_id'},
				{mapping: 'table_name', text: 'table_name'},
			],
			OnDataLoading: async () => {
				var currentconfig = null;
				if (typeof fn_get_currentconfig === 'function') {
					currentconfig = fn_get_currentconfig();
				} else {
					throw new Error('salah mendefinisikan fn_get_currentconfig.')
				}
				return await $ui.schema_gettablelist(currentconfig);
			},
			OnDataLoaded : async (result, options) => {},
			OnSelected: async (value, display, record) => {
				var currentconfig = null;
				if (typeof fn_get_currentconfig === 'function') {
					currentconfig = fn_get_currentconfig();
				} else {
					throw new Error('salah mendefinisikan fn_get_currentconfig.')
				}

				tabledata[tableid].fieldbox.show();
				obj[tablecboname].fields = await $ui.schema_getfields(currentconfig, value);
				for (var name of tabledata[tableid].fields) {
					obj[name].combo('setValue', '');
					obj[name].combo('setText', '');
				}

				if (typeof fn_tablename_selected === 'function') {
					fn_tablename_selected(tableid)
				}
			}
		});	
	}
}


export function setFieldSelector(fn_get_currentconfig, tablecbo, tabledata, obj, this_page_id) {
	for (let tableid in tabledata) {
		let tablecboname = tabledata[tableid].tablecboname;
		for (let cboname of  tabledata[tableid].fields) {
			if (tablecbo.includes(cboname)) continue;
			var iscombo = obj[cboname].hasClass('easyui-combo') ? true : false;
			if (!iscombo) continue;
			var cbo = obj[cboname];

			cbo.name = cbo.attr('id');
			cbo.title = cbo.attr('title');
			new fgta4slideselect2(cbo, {
				title: 'Pilih Field ' + ((cbo.title==null) ? '' : ' untuk ' + cbo.title),
				returnpage: this_page_id,
				
				fieldValue: 'field_id',
				fieldValueMap: 'field_name',
				fieldDisplay: 'field_name',
				fields: [
					{mapping: 'field_id', text: 'field_id'},
					{mapping: 'field_name', text: 'field_name'},
				],
				OnDataLoading: async () => {
					if (obj[tablecboname].fields==null) {
						obj[tablecboname].fields = [];
					}

					if (obj[tablecboname].fields.length==0) {
						var currentconfig = null;
						if (typeof fn_get_currentconfig === 'function') {
							currentconfig = fn_get_currentconfig();
						} else {
							throw new Error('salah mendefinisikan fn_get_currentconfig.')
						}
						var value = obj[tablecboname].combo('getValue');
						obj[tablecboname].fields = await $ui.schema_getfields(currentconfig, value);
					} 
					return obj[tablecboname].fields;
				},
				OnDataLoaded : async (result, options) => {},
				OnSelected: async (value, display, record) => {
				}
			});	
		}
	}
}