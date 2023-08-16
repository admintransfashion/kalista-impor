'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Media Order",
	autoid: true,
	idprefix: 'MO',
	printing: true,
	icon : "icon-mediaorder-white.svg",
	backcolor : "#744141",
	committer: true,
	approval: true,
	doc_id: 'MEDIAORDER',
	buttons: {
		btn_verify: {},
		btn_close: {}
	},

	persistent: {
		'trn_mediaorder': {
			comment: 'Daftar Media Order',
			primarykeys: ['mediaorder_id'],
			data: {
				mediaorder_id: { text: 'ID', type: dbtype.varchar(30), null: false, options: { required: true, invalidMessage: 'ID harus diisi' } },


				project_id: {
					text: 'Project', type: dbtype.varchar(30), null: false,
					options: { required: true, invalidMessage: 'Project harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_project',
						field_value: 'project_id', field_display: 'project_name',
						api: 'finact/master/project/list'
					})
				},


				
				orderintype_id: {
					text:'Order Type', type: dbtype.varchar(10), null:true, suppresslist: true,
					options: { required: true, invalidMessage: 'Order Type Harus diisi', disabled:false } ,
					comp: comp.Combo({
						table: 'mst_orderintype', 
						field_value: 'orderintype_id', field_display: 'orderintype_name',  field_display_name: 'orderintype_name',
						api: 'finact/sales/orderintype/list',
						OnSelectedScript: `
				form.setValue(obj.cbo_arunbill_coa_id, record.arunbill_coa_id==null?'--NULL--':record.arunbill_coa_id, record.arunbill_coa_name);
				form.setValue(obj.cbo_ar_coa_id, record.ar_coa_id==null?'--NULL--':record.ar_coa_id, record.ar_coa_name);
				form.setValue(obj.cbo_dp_coa_id, record.dp_coa_id==null?'--NULL--':record.dp_coa_id, record.dp_coa_name);
				form.setValue(obj.cbo_sales_coa_id, record.sales_coa_id==null?'--NULL--':record.sales_coa_id, record.sales_coa_name);
				form.setValue(obj.cbo_salesdisc_coa_id, record.salesdisc_coa_id==null?'--NULL--':record.salesdisc_coa_id, record.salesdisc_coa_name);
				form.setValue(obj.cbo_ppn_coa_id, record.ppn_coa_id==null?'--NULL--':record.ppn_coa_id, record.ppn_coa_name);
				form.setValue(obj.cbo_ppnsubsidi_coa_id, record.ppnsubsidi_coa_id==null?'--NULL--':record.ppnsubsidi_coa_id, record.ppnsubsidi_coa_name);
				form.setValue(obj.cbo_pph_coa_id, record.pph_coa_id==null?'--NULL--':record.pph_coa_id, record.pph_coa_name);						
						`
					})
				},

				mediaorder_date: {text:'Date', type: dbtype.date, null:false},
				mediaorder_descr: { text: 'Descr', type: dbtype.varchar(255), null: false, options: { required: true, invalidMessage: 'Descr harus diisi' } },
				mediaorder_ref: { text: 'Ref', type: dbtype.varchar(90), null: false },

				dept_id: {
					text: 'Dept', type: dbtype.varchar(30), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'Departemen harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_dept',
						field_value: 'dept_id', field_display: 'dept_name',
						api: 'ent/organisation/dept/list-byuser'
					})
				},

				ae_empl_id: {
					text:'AE', type: dbtype.varchar(14), null:true, suppresslist: true,
					options:{prompt:'NONE'},
					comp: comp.Combo({
						table: 'mst_empl', 
						field_value: 'empl_id', field_display: 'empl_name',  field_display_name: 'ae_empl_name',
						api: 'hrms/master/empl/list'})
				},

				agency_partner_id: {
					suppresslist: true,
					options:{required:true,invalidMessage:'Agency harus diisi', prompt:'-- PILIH --'},
					text:'Agency', type: dbtype.varchar(30), null:false, 
					comp: comp.Combo({
						table: 'mst_partner', 
						field_value: 'partner_id', field_display: 'partner_name', field_display_name: 'agency_partner_name', 
						api: 'ent/affiliation/partner/list',
						criteria: {
							partnertype_id: 'AGENCY'
						}
					
					})
				},	
				
				advertiser_partner_id: {
					suppresslist: true,
					options:{required:true,invalidMessage:'Adveriser harus diisi', prompt:'-- PILIH --'},
					text:'Advertiser', type: dbtype.varchar(30), null:false, 
					comp: comp.Combo({
						table: 'mst_partner', 
						field_value: 'partner_id', field_display: 'partner_name', field_display_name: 'advertiser_partner_name', 
						api: 'ent/affiliation/partner/list',
						criteria: {
							partnertype_id: 'ADVERTISER'
						}
					})
				},

				mediaorder_traffic: { text: 'Traffic Code', type: dbtype.varchar(90), null: false, suppresslist: true },
				mediaorder_status: { text: 'Status', type: dbtype.varchar(90), null: false , suppresslist: true},
				mediaorder_direct: { text: 'Direct', type: dbtype.varchar(90), null: false , suppresslist: true},
				mediaorder_bundling: { text: 'Bundling', type: dbtype.varchar(90), null: false , suppresslist: true},







				orderin_totalqty: { text: 'Total Qty Spot', type: dbtype.int(5), null: false, default:0, suppresslist: true },
				orderin_totalitem: { text: 'Total Baris', type: dbtype.int(5), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_salesgross: { text: 'Gross Sales', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_discount: { text: 'Dicount', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_subtotal: { text: 'Sub Total', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_pph: { text: 'PPh', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_nett: { text: 'Sales Nett', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_ppn: { text: 'PPN', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_total: { text: 'Total', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_totaladdcost: { text: 'Additional Cost', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },
				orderin_payment: { text: 'Total Payment', type: dbtype.decimal(16,0), null: false, default:0, suppresslist: true, options: { disabled: true} },



				arunbill_coa_id: { 
					section: section.Begin('Chart of Accounts'),  // , 'defbottomborder'
					text: 'COA AR Unbill', type: dbtype.varchar(17), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'AR harus diisi' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'arunbill_coa_name', 
						api: 'finact/master/coa/list'})				
				
				},
				ar_coa_id: { 
					text: 'COA AR', type: dbtype.varchar(17), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'AR harus diisi' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'ar_coa_name', 
						api: 'finact/master/coa/list'})				
				
				},

	
				dp_coa_id: { 
					text: 'COA Downpayment', type: dbtype.varchar(17), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'OrderIn COA Downpayment harus diisi' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'dp_coa_name', 
						api: 'finact/master/coa/list'})				
				
				},

				sales_coa_id: { 
					text: 'COA Sales', type: dbtype.varchar(17), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'OrderIn Sales COA harus diisi' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'sales_coa_name', 
						api: 'finact/master/coa/list'})				
				
				},	
				
				salesdisc_coa_id: { 
					text: 'COA Disc Sales', type: dbtype.varchar(17), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'salesdisc_coa_name', 
						api: 'finact/master/coa/list'})				
				},

				ppn_coa_id: { 
					text: 'COA PPN Payable', type: dbtype.varchar(17), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'ppn_coa_name', 
						api: 'finact/master/coa/list'})				
				},

				ppnsubsidi_coa_id: { 
					text: 'COA Subsidi PPN', type: dbtype.varchar(17), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					tips: 'Apabila PPN include COA ini perlu diisi',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'ppnsubsidi_coa_name', 
						api: 'finact/master/coa/list'})				
				},

				pph_coa_id: { 
					section: section.End(),
					text: 'COA PPH Prepaid', type: dbtype.varchar(17), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					tips: '',
					tipstype: 'visible',
					comp: comp.Combo({
						table: 'mst_coa', 
						field_value: 'coa_id', field_display: 'coa_name', field_display_name: 'pph_coa_name', 
						api: 'finact/master/coa/list'})				
				},		
				
				

				trxmodel_id: { 
					text: 'Transaksi', type: dbtype.varchar(10), null: false, suppresslist: true, 
					options: { required: true, invalidMessage: 'Model Transaksi harus diisi', disabled: true }, 
					initialvalue: {id:'SAL', display:'SALES'},
					comp: comp.Combo({
						table: 'mst_trxmodel', 
						field_value: 'trxmodel_id', field_display: 'trxmodel_name', field_display_name: 'trxmodel_name', 
						api: 'finact/master/trxmodel/list'})				
				
				},

				doc_id: {
					text:'Doc', type: dbtype.varchar(30), null:false, uppercase: true, suppresslist: true, 
					options: {required:true, invalidMessage:'ID harus diisi', disabled: true },
					initialvalue: {id:'MEDIAORDER', display:'MEDIAORDER'},
					comp: comp.Combo({
						table: 'mst_doc',
						field_value: 'doc_id', field_display: 'doc_name', field_display_name: 'doc_name',
						api: 'ent/organisation/docs/list'
					})				
				},

				mediaorder_version: {text:'Version', type: dbtype.int(4), null:false, default:'0', suppresslist: true, options:{disabled:true}},
				mediaorder_iscommit: {text:'Commit', type: dbtype.boolean, null:false, default:'0', unset:true, options:{disabled:true}},
				mediaorder_commitby: {text:'CommitBy', type: dbtype.varchar(14), suppresslist: true, unset:true, options:{disabled:true}, hidden: true, lookup:'user'},
				mediaorder_commitdate: {text:'CommitDate', type: dbtype.datetime, suppresslist: true, unset:true, comp:comp.Textbox(), options:{disabled:true}, hidden: true},	

				mediaorder_isapprovalprogress: {text:'Progress', type: dbtype.boolean, null:false, default:'0', unset:true, suppresslist: true, options:{disabled:true}, hidden: true},
				mediaorder_isapproved: { text: 'Approved', type: dbtype.boolean, null: false, default: '0', unset:true, suppresslist: true, options: { disabled: true } },
				mediaorder_approveby: { text: 'Approve By', type: dbtype.varchar(14), suppresslist: true, unset:true, options: { disabled: true }, hidden: true, lookup:'user' },
				mediaorder_approvedate: { text: 'Approve Date', type: dbtype.datetime, suppresslist: true, unset:true, comp: comp.Textbox(), options: { disabled: true }, hidden: true },
				mediaorder_isdeclined: { text: 'Declined', type: dbtype.boolean, null: false, default: '0', unset:true, suppresslist: true, options: { disabled: true } },
				mediaorder_declineby: { text: 'Decline By', type: dbtype.varchar(14), suppresslist: true, unset:true, options: { disabled: true }, hidden: true, lookup:'user' },
				mediaorder_declinedate: { text: 'Decline Date', type: dbtype.datetime, suppresslist: true, unset:true, comp: comp.Textbox(), options: { disabled: true }, hidden: true },
				mediaorder_notes: { text: 'Notes', type: dbtype.varchar(255), null: true, suppresslist: true, hidden: true },

				mediaorder_isclose: {text:'Close', type: dbtype.boolean, null:false, default:'0', suppresslist: true, unset:true, options:{disabled:true}},
				mediaorder_closeby: {text:'Close By', type: dbtype.varchar(14), suppresslist: true, unset:true, options:{disabled:true}, hidden: true, lookup:'user'},
				mediaorder_closedate: {text:'Close Date', type: dbtype.datetime, suppresslist: true, unset:true, comp:comp.Textbox(), options:{disabled:true}, hidden: true},	

			},
			
			defaultsearch: ['mediaorder_id', 'mediaorder_descr']
		},

		'trn_mediaorderitem' : {
			comment: 'Meida Order Item',
			primarykeys: ['mediaorderitem_id'],		
			data: {
				mediaorderitem_id: { text: 'ID', type: dbtype.varchar(14), null: false, suppresslist: true, },
				itemclass_id: {
					text:'Class', type: dbtype.varchar(14), null:false, suppresslist: true,
					options: { required: true, invalidMessage: 'Class harus diisi' } ,
					comp: comp.Combo({
						table: 'mst_itemclass', 
						field_value: 'itemclass_id', field_display: 'itemclass_name', field_display_name: 'itemclass_name', 
						api: 'finact/items/itemclass/list',
						criteria: {
							itemmodel_id: 'ADS'
						}
					})					
				},


				brand_id: { 
					text: 'Brand', type: dbtype.varchar(14), uppercase: true, null: false, 
					options: { required: true, invalidMessage: 'Brand harus diisi' }, 
					comp: comp.Combo({
						table: 'mst_brand',
						field_value: 'brand_id', field_display: 'brand_name',
						api: 'ent/affiliation/brand/list'
					})				
				},


				mediaorderitem_spot: { text: 'Spot Code', type: dbtype.varchar(90), null: false },
				mediaorderitem_descr: { text: 'Descr', type: dbtype.varchar(90), null: false, options: { required: true, invalidMessage: 'Descr harus diisi' } },
				mediaorderitem_validr: { text: 'Value IDR', type: dbtype.decimal(14, 2), null: false, default: 0, suppresslist: true, options: { required: true } },

				projbudget_id: {
					text: 'Budget', type: dbtype.varchar(30), null:true,  suppresslist: true,
					options: { required: true, invalidMessage: 'Budget harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_projbudget',
						field_value: 'projbudget_id', field_display: 'projbudget_name',
						api: 'finact/budget/projbudget/list',
						OnSelectedScript: `
				form.setValue(obj.cbo_project_id, record.project_id, record.project_name);			
						`
					})
				},

				projbudgettask_id: {
					text: 'Budget Task', type: dbtype.varchar(14), null:true,  suppresslist: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'mst_projbudgettask',
						field_value: 'projbudgettask_id', field_display: 'projbudgettask_name', field_display_name:'projbudgettask_name',
						api: 'finact/budget/projbudget/task-list',
						staticfilter: `
				criteria.projbudget_id = form.getValue(obj.cbo_projbudget_id);		
						`,
						OnSelectedScript: `
				form.setValue(obj.cbo_projecttask_id, record.projecttask_id, record.projecttask_name);			
						`
					})
				},

				project_id: {
					text: 'Project', type: dbtype.varchar(30), null: true,
					options: { required: true, invalidMessage: 'Project harus diisi', prompt: '-- PILIH --', disabled: true },
					comp: comp.Combo({
						table: 'mst_project',
						field_value: 'project_id', field_display: 'project_name',
						api: 'finact/master/project/list'
					})
				},

				projecttask_id: {
					text: 'Project Task', type: dbtype.varchar(14), null: true, suppresslist: true,
					options: { prompt: 'NONE', disabled: true },
					comp: comp.Combo({
						table: 'mst_projecttask',
						field_value: 'projecttask_id', field_display: 'projecttask_name',
						api: 'finact/master/projecttask/list-byproject'
					})
				},


				mediaorder_id: { text: 'Order', type: dbtype.varchar(30), null: false, uppercase: true },
			}	
		}


	},

	schema: {
		header: 'trn_mediaorder',
		detils: {
			'items': {title: 'Items', table: 'trn_mediaorderitem', form: true, headerview: 'mediaorder_descr' },
			'uploads': {title: 'Uploads', table: 'trn_mediaorderitem', form: false, headerview: 'mediaorder_descr' }
		}
	}


}