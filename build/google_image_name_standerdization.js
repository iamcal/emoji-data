/**
 * This script fixes two issues with the google image folder.
 *
 * First:
 * Our emoji image filenames are unique strings of unicode points that match the emoji
 * image they represent. For instance, the unciode sequence for :thumbsup::skin-tone-2: (👍🏻)
 * is `\U+1f44d\U+1f3fb` and the image filename is `1f44d-1f3fb.png`
 *
 * Some emojis use the 'Variation Selector-16' unicode point: `U+FEOF`. However, Filenames for
 * google's emojis do not include the `U+FEOF` point, while apple and other platforms do.
 * For example: the unicode sequence for :man-heart-man: is U+1F468 U+200D U+2764 U+FE0F U+200D U+1F468
 * The apple filename is:  1f468-200d-2764-fe0f-200d-1f468.png
 * The google filename is: 1f468-200d-2764-200d-1f468.png (the google filename has stripped the `fe0f` sequence)
 *
 * Since we expect standard filename across platforms, this script renames the google filenames to match the apple ones.
 * We do this by using the apple filenames to create a map from filenames stripped of `fe0f` strings
 * to their complete form, and then use that map to rename the google files.
 *
 * Second:
 * Some emojis have new gendered forms and their originals have become obsolete. For instance couplekiss (1F48F) has
 * been obsoleted by woman_kiss_man (1f469-200d-2764-fe0f-200d-1f48b-200d-1f468) and the two emojis use the same image asset.
 * In the apple image folder we have a duplicate asset for each emoji (aka. `1F48F.png` and `1f469-200d-2764-fe0f-200d-1f48b-200d-1f468.png`).
 * The google image folder only has a copy for the newer emoji (aka. `1F48F.png` is missing in the google folder leading us to think google
 * does not support this emoji). For our build script to work, We need the google image folder to match the apple one, so we use the data
 * in `data_obsoleted.txt` to duplicate the current emoji asset with the name of the obsolete one.
 *
 * This script needs to be rerun anytime we bring in an new batch of emoji image files form the Google Noto repo
 */
const fs = require('fs');

const appleDir = '../img-apple-64';
const googleDir64 = '../img-google-64';
const googleDir136 = '../img-google-136'

var mapFromIncompleteToStandard = {};
var mapFromCurrentToObsoleteFilenames = {};
var renamedFileCount = 0;
var copiedFileCount = 0;

/**
 * Creates a map from filenames without the 'fe0f' unicode point
 * to the complete filename with the unicode point
 * ex: '1f468-200d-2764-200d-1f468.png' maps to '1f468-200d-2764-fe0f-200d-1f468.png'
 *
 * @return {null}
 */
var generateFilenameMap = function() {
	fs.readdirSync(appleDir).forEach(filename => {
		var incompleteFilename = filename.replace(/-fe0f/g, '');
		if (mapFromIncompleteToStandard[incompleteFilename]) {
			// we expect the mapping from incomplete filenames to
			// standard filenames to be unique.
			console.error('We have a non-unqie filename in our map. This will cause missing images for Android. Overlapping files: ', mapFromIncompleteToStandard[incompleteFilename], filename);
		}

		if (incompleteFilename !== filename) {
			mapFromIncompleteToStandard[incompleteFilename] = filename;
		}
	});
}

/**
 * Uses the data in 'data_obsoleted.txt' to map current emojis to the ones
 * they obsolete
 * ex: '1f469-200d-2764-fe0f-200d-1f48b-200d-1f468.png' maps to '1f48f.png'
 *
 * @return {null}
 */
var generateObsoleteFilenameData = function() {
	var fileData = fs.readFileSync('data_obsoleted.txt', 'utf8');
	var fileDataLines = fileData.split('\n');
	var filenameMap = {};
	fileDataLines.forEach(function(line) {
		if (!line || line == '#') return;

		stripLine = line.substring(0, line.indexOf(' '));
		var indexOfSeperator = stripLine.indexOf(';');
		var obsoleteKey = stripLine.substring(0, indexOfSeperator);
		var currentKey = stripLine.substring(indexOfSeperator + 1, stripLine.length);

		var currentFilename = currentKey + '.png';
		var obsoleteFilename = obsoleteKey + '.png';

		mapFromCurrentToObsoleteFilenames[currentFilename] = obsoleteFilename;
	});
}

/**
 * Renames any google file missing a 'fe0f' to include it
 * and match the apple file name convention. Adds any missing
 * obsolete image assets.
 *
 * @param  {String} googleDir - directory with google emoji images to be renamed
 * @return {null}
 */
var renameGoogleImgFiles = function(googleDir) {
	fs.readdirSync(googleDir).forEach(filename => {
		var completeFilename = mapFromIncompleteToStandard[filename];

		if (completeFilename && filename != completeFilename) {
			var oldPath = `${googleDir}/${filename}`;
			var newPath = `${googleDir}/${completeFilename}`;
			fs.renameSync(oldPath, newPath);
			renamedFileCount++;
		}

		var currentFilename = completeFilename || filename;
		var obsoleteFilename = mapFromCurrentToObsoleteFilenames[currentFilename];
		if (obsoleteFilename && obsoleteFilename != currentFilename) {
			var existingFilepath = `${googleDir}/${currentFilename}`;
			var newFilePath = `${googleDir}/${obsoleteFilename}`;
			copyFileToNewPath(existingFilepath, newFilePath);
		}
	});
}

/**
 * Copies the source file to the new directory
 * @param  {String} sourceFile - path to source file
 * @param  {String} newName    - path to copy file to
 * @return {null}
 */
var copyFileToNewPath = function(sourceFilePath, newFilePath) {
	try {
		// if source file does not exist return
		if (!fs.existsSync(sourceFilePath)) return;
		// if we would overwrite and existing file, return
		if (fs.existsSync(newFilePath)) return;
		// copy the file to the new destination
		fs.createReadStream(sourceFilePath).pipe(fs.createWriteStream(newFilePath));
		copiedFileCount++;
	} catch (e) {
		console.error('Error copying file with new name: ', sourceFilePath, newFilePath, " with the following error: ", e);
	}
}

generateFilenameMap();
generateObsoleteFilenameData();

renameGoogleImgFiles(googleDir64);
renameGoogleImgFiles(googleDir136);

console.log('Google image standardization process complete.');
if (renamedFileCount) console.log(`${renamedFileCount} google files have been renamed to match the standard naming convention.`);
if (copiedFileCount) console.log(`${copiedFileCount} google files have been duplicated for emojis which have been 'obsoleted'`);

