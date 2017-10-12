const appleDir = '../img-apple-64';
const googleDir = '../img-google-64/';

const fs = require('fs');

var mapFromIncompleteToStandard = {};
var count = 0;

fs.readdirSync(appleDir).forEach(filename => {
	var incompleteFilename = filename.replace(/-fe0f/g, '');
	if (mapFromIncompleteToStandard[incompleteFilename]) {
		console.error('We have a non-unqie filename in our map. This will cause missing images.');
	}

	if (incompleteFilename !== filename) {
		mapFromIncompleteToStandard[incompleteFilename] = filename;
		count++;
	}
});

var countRenamed = 0;
var mapCopy = Object.assign({}, mapFromIncompleteToStandard);
fs.readdirSync(googleDir).forEach(filename => {
	var completeFilename = mapFromIncompleteToStandard[filename];

	if (completeFilename && completeFilename && filename != completeFilename) {
		delete mapCopy[filename];
		console.log(filename, completeFilename);
		var oldPath = `${googleDir}/${filename}`;
		var newPath = `${googleDir}/${completeFilename}`;
		fs.renameSync(oldPath, newPath);
		countRenamed++;
	}
});

console.log('missing names: ', count);
console.log('countRenamed: ', countRenamed);
console.log(mapCopy);


