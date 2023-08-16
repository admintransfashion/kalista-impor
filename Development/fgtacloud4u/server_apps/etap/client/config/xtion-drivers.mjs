const localapi = window.parent.getLocalAPI();
const path = localapi.path;
const fs = localapi.fs;

export async function update(drivername) {
	var drivepath = path.join(localapi.rootPath, 'drv');
	if (!fs.existsSync(drivepath)) {
		fs.mkdirSync(drivepath);
	}
	
	
	var filename = path.join(drivepath, 'mysql.js');
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState == XMLHttpRequest.DONE) {
			fs.writeFileSync(filename, xhr.responseText);
		}
	}
	xhr.open('GET', 'http://local.fgta.net/fgta/index.php/asset/etap/client/drivers/mysql.js', true);
	xhr.send(null);




}

