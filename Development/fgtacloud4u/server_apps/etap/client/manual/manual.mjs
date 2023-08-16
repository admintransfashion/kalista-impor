


export async function init(opt) {
	await PreloadData()
}

async function PreloadData() {
	console.log('loading PDF...');

	var url = 'index.php/asset/etap/client/manual/manual.pdf';
	var pdfurlpath = `../../../../${url}`;
	renderPDF(url, pdfurlpath, document.getElementById('pdfcontainer'));

	var maincontainer_el = document.getElementById('pdfdocument');
	maincontainer_el.style.paddingLeft = '0px';
	maincontainer_el.style.paddingRight = '0px';
	maincontainer_el.style.paddingTop = '0px';
	maincontainer_el.style.paddingBottom = '0px';

	// var pdfcontainer_el = document.getElementById('pdfcontainer');
	
	// pdfcontainer_el.style.width = `${res.hd_size.width}px`
	// pdfcontainer_el.style.paddingTop = '5px';
}


function renderPDF(downloadurl, pdfurlpath, canvasContainer, options) {
	var w = window.innerWidth;



	// var md = document.getElementById('pdfdocument');
	var options = options || { scale: 1 };
	var md_width_string = `${w-5}px`	;
	
 
    function renderPage(page) {
        var viewport = page.getViewport(options.scale);
		var canvas = document.createElement('canvas');

		canvas.style.width = md_width_string;
		

		console.log(viewport);

		canvas.height = viewport.height;
        canvas.width = viewport.width;

        var ctx = canvas.getContext('2d');
        var renderContext = {
          canvasContext: ctx,
          viewport: viewport
        };
		
		var pagecontainer = document.createElement('div');
		pagecontainer.classList.add('pagecontainer');
		pagecontainer.appendChild(canvas);

        canvasContainer.appendChild(pagecontainer);
        
		page.render(renderContext);
    }

	
    function renderPages(pdfDoc) {
        for(let num = 1; num <= pdfDoc.numPages; num++) {
			pdfDoc.getPage(num).then((page)=>{
				renderPage(page);
				if (num==pdfDoc.numPages) {
					var loader_el = document.getElementById('loader');
					loader_el.style.display = 'none';
					
					var md = document.getElementById('pdfdocument');
					var md_size = canvasContainer.getBoundingClientRect();

					var maincontent_height = Math.ceil(md_size.height);
					md.style.height = `${maincontent_height}px`;

					var fd = document.getElementById('pdfdocument');


					// tambahkan download catalog
					var divDwnContainer = document.createElement('div')
					var aDwnContainer = document.createElement('a')

					aDwnContainer.href = downloadurl
					aDwnContainer.target = '_blank'
					aDwnContainer.appendChild(document.createTextNode('download this catalog'))
					aDwnContainer.classList.add('download-catalog');

					divDwnContainer.appendChild(aDwnContainer);
					divDwnContainer.classList.add('download-container');

					fd.parentNode.insertBefore(divDwnContainer,  fd);
				}
			});
		}

    }

    pdfjsLib.disableWorker = true;
    pdfjsLib.getDocument(pdfurlpath).then(renderPages);

} 