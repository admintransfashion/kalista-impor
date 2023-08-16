'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
  title: "Media Order Type",
  autoid: false,

  persistent: {
    'mst_mediaordertype': {
		comment: 'Daftar tipe-tipe Salmediaes Order',
		primarykeys: ['mediaordertype_id'],
		data: {
			mediaordertype_id: { text: 'ID', type: dbtype.varchar(10), uppercase: true, null: false, options: { required: true, invalidMessage: 'ID harus diisi' } },
			mediaordertype_name: { text: 'Type Media Order', type: dbtype.varchar(30), uppercase: true, options: { required: true, invalidMessage: 'Tipe Media Order harus diisi' } },
			mediaordertype_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: true }
		},
		uniques: {
       		'mediaordertype_name': ['mediaordertype_name']
      	},
		defaultsearch: ['mediaordertype_id', 'mediaordertype_name'],
		
		values: [
			{mediaordertype_id:'BRT', mediaordertype_name:'BARTER'},
			{mediaordertype_id:'BLO', mediaordertype_name:'BLOCKING'},
			{mediaordertype_id:'CPR', mediaordertype_name:'CPRP'},
			{mediaordertype_id:'RTL', mediaordertype_name:'RETAIL'},
			{mediaordertype_id:'SPO', mediaordertype_name:'SPONSOR'}
		]
    }
  },

  schema: {
    header: 'mst_mediaordertype',
    detils: {
    }
  }
}