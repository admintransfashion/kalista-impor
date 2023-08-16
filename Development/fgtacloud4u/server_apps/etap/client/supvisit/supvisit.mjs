import {fgta4grid} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4grid.mjs'
import {fgta4form} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4form.mjs'
import * as fgta4pages from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4pages.mjs'
import * as fgta4pageslider from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4pageslider.mjs'
import * as apis from './supvisit.apis.mjs'
import * as pList from './supvisit-list.mjs'
import * as pEdit from './supvisit-edit.mjs'
import * as pEditSitegrid from './supvisit-sitegrid.mjs'
import * as pEditSiteform from './supvisit-siteform.mjs'
import * as pEditUsergrid from './supvisit-usergrid.mjs'
import * as pEditUserform from './supvisit-userform.mjs'



const pnl_list = $('#pnl_list')
const pnl_edit = $('#pnl_edit')
const pnl_editsitegrid = $('#pnl_editsitegrid')
const pnl_editsiteform = $('#pnl_editsiteform')
const pnl_editusergrid = $('#pnl_editusergrid')
const pnl_edituserform = $('#pnl_edituserform')



var pages = fgta4pages;
var slider = fgta4pageslider;


export const SIZE = {width:0, height:0}


export async function init(opt) {
	// $ui.grd_list = new fgta4grid()
	// $ui.grd_edit = new fgta4grid()

	global.fgta4grid = fgta4grid
	global.fgta4form = fgta4form

	$ui.apis = apis
	document.getElementsByTagName("body")[0].style.margin = '5px 5px 5px 5px'

	pages
		.setSlider(slider)
		.initPages([
			{panel: pnl_list, handler: pList},
			{panel: pnl_edit, handler: pEdit},
			{panel: pnl_editsitegrid, handler: pEditSitegrid},
			{panel: pnl_editsiteform, handler: pEditSiteform},
			{panel: pnl_editusergrid, handler: pEditUsergrid},
			{panel: pnl_edituserform, handler: pEditUserform}			
		], opt)

	$ui.setPages(pages)


	document.addEventListener('OnButtonHome', (ev) => {
		if (ev.detail.cancel) {
			return
		}

		ev.detail.cancel = true;
		ExitModule();
	})
	
	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	})	



	await PreloadData()

}


export function OnSizeRecalculated(width, height) {
	SIZE.width = width
	SIZE.height = height
}

export async function ExitModule() {
	$ui.home();
}



async function PreloadData() {
	
}