'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
  title: "Channel",
  autoid: false,

  persistent: {
    'mst_channel': {
		comment: 'Daftar Channel Media',
		primarykeys: ['channel_id'],
		data: {
			channel_id: { text: 'ID', type: dbtype.varchar(10), uppercase: true, null: false, options: { required: true, invalidMessage: 'ID harus diisi' } },
			channel_name: { text: 'Nama Channel', type: dbtype.varchar(30), uppercase: true, options: { required: true, invalidMessage: 'Channel harus diisi' } },
			channel_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: true }
		},
		uniques: {
       		'channel_name': ['channel_name']
      	},
		defaultsearch: ['channel_id', 'channel_name'],
		
		values: [
			{channel_id:'MAIN', channel_name:'MAIN'},
		]
    }
  },

  schema: {
    header: 'mst_channel',
    detils: {
    }
  }
}