// wordSearchWorker.js
self.onmessage = function (e) {
  if (e.data.type === "GENERATE_PUZZLE") {
    const settings = e.data.settings;
    const allowedDirections = ["W", "N", "WN"];
    settings.directions = settings.directions.filter((d) =>
      allowedDirections.includes(d)
    );
    // Build the matrix and return it
    const result = generatePuzzleData(settings);

    // Post the results back
    self.postMessage({
      type: "PUZZLE_READY",
      matrix: result.matrix,
      wordsList: result.wordsList,
    });
  }
};

// Keep track of whether we've detected any Polish diacritics
let puzzleHasPolish = false;

/**
 * Quick helper: returns true if a string has at least one Polish diacritic:
 *   ĄąĆćĘęŁłŃńÓóŚśŹźŻż
 */
function containsPolishCharacters(str) {
  return /[ĄąĆćĘęŁłŃńÓóŚśŹźŻż]/.test(str);
}

// Provide rangeInt, removeDiacritics, and searchLanguage
// because the worker can’t see them from outside.
if (typeof Math.rangeInt !== "function") {
  Math.rangeInt = function (min, max) {
    if (max === undefined) {
      max = min;
      min = 0;
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
  };
}

const directionsMap = {
  W: [0, 1], // Horizontal: left-to-right
  N: [1, 0], // Vertical: top-to-bottom
  WN: [1, 1], // Diagonal: top-left to bottom-right
  EN: [1, -1], // Diagonal: top-right to bottom-left
};

// Polish alphabet (uppercase) for random fill if puzzleHasPolish
// Standard Polish does not typically use Q, V, X, but adjust if you like.
const POLISH_ALPHABET = [
  "A",
  "Ą",
  "B",
  "C",
  "Ć",
  "D",
  "E",
  "Ę",
  "F",
  "G",
  "H",
  "I",
  "J",
  "K",
  "L",
  "Ł",
  "M",
  "N",
  "Ń",
  "O",
  "Ó",
  "P",
  "R",
  "S",
  "Ś",
  "T",
  "U",
  "W",
  "Y",
  "Z",
  "Ź",
  "Ż",
];

// Put your removeDiacritics and searchLanguage EXACTLY as they were:
var defaultDiacriticsRemovalMap = [
  {
    base: "A",
    letters:
      /(&#65;|&#9398;|&#65313;|&#192;|&#193;|&#194;|&#7846;|&#7844;|&#7850;|&#7848;|&#195;|&#256;|&#258;|&#7856;|&#7854;|&#7860;|&#7858;|&#550;|&#480;|&#196;|&#478;|&#7842;|&#197;|&#506;|&#461;|&#512;|&#514;|&#7840;|&#7852;|&#7862;|&#7680;|&#260;|&#570;|&#11375;|[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F])/g,
  },
  {
    base: "AA",
    letters: /(&#42802;|[\uA732])/g,
  },
  {
    base: "AE",
    letters: /(&#198;|&#508;|&#482;|[\u00C6\u01FC\u01E2])/g,
  },
  {
    base: "AO",
    letters: /(&#42804;|[\uA734])/g,
  },
  {
    base: "AU",
    letters: /(&#42806;|[\uA736])/g,
  },
  {
    base: "AV",
    letters: /(&#42808;|&#42810;|[\uA738\uA73A])/g,
  },
  {
    base: "AY",
    letters: /(&#42812;|[\uA73C])/g,
  },
  {
    base: "B",
    letters:
      /(&#66;|&#9399;|&#65314;|&#7682;|&#7684;|&#7686;|&#579;|&#386;|&#385;|[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181])/g,
  },
  {
    base: "C",
    letters:
      /(&#67;|&#9400;|&#65315;|&#262;|&#264;|&#266;|&#268;|&#199;|&#7688;|&#391;|&#571;|&#42814;|[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E])/g,
  },
  {
    base: "D",
    letters:
      /(&#68;|&#9401;|&#65316;|&#7690;|&#270;|&#7692;|&#7696;|&#7698;|&#7694;|&#272;|&#395;|&#394;|&#393;|&#42873;|&#208;|[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779\u00D0])/g,
  },
  {
    base: "DZ",
    letters: /(&#497;|&#452;|[\u01F1\u01C4])/g,
  },
  {
    base: "Dz",
    letters: /(&#498;|&#453;|[\u01F2\u01C5])/g,
  },
  {
    base: "E",
    letters:
      /(&#69;|&#9402;|&#65317;|&#200;|&#201;|&#202;|&#7872;|&#7870;|&#7876;|&#7874;|&#7868;|&#274;|&#7700;|&#7702;|&#276;|&#278;|&#203;|&#7866;|&#282;|&#516;|&#518;|&#7864;|&#7878;|&#552;|&#7708;|&#280;|&#7704;|&#7706;|&#400;|&#398;|[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E])/g,
  },
  {
    base: "F",
    letters:
      /(&#70;|&#9403;|&#65318;|&#7710;|&#401;|&#42875;|[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B])/g,
  },
  {
    base: "G",
    letters:
      /(&#71;|&#9404;|&#65319;|&#500;|&#284;|&#7712;|&#286;|&#288;|&#486;|&#290;|&#484;|&#403;|&#42912;|&#42877;|&#42878;|[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E])/g,
  },
  {
    base: "H",
    letters:
      /(&#72;|&#9405;|&#65320;|&#292;|&#7714;|&#7718;|&#542;|&#7716;|&#7720;|&#7722;|&#294;|&#11367;|&#11381;|&#42893;|[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D])/g,
  },
  {
    base: "I",
    letters:
      /(&#73;|&#9406;|&#65321;|&#204;|&#205;|&#206;|&#296;|&#298;|&#300;|&#304;|&#207;|&#7726;|&#7880;|&#463;|&#520;|&#522;|&#7882;|&#302;|&#7724;|&#407;|[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197])/g,
  },
  {
    base: "J",
    letters:
      /(&#74;|&#9407;|&#65322;|&#308;|&#584;|[\u004A\u24BF\uFF2A\u0134\u0248])/g,
  },
  {
    base: "K",
    letters:
      /(&#75;|&#9408;|&#65323;|&#7728;|&#488;|&#7730;|&#310;|&#7732;|&#408;|&#11369;|&#42816;|&#42818;|&#42820;|&#42914;|[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2])/g,
  },
  {
    base: "L",
    letters:
      /(&#76;|&#9409;|&#65324;|&#319;|&#313;|&#317;|&#7734;|&#7736;|&#315;|&#7740;|&#7738;|&#321;|&#573;|&#11362;|&#11360;|&#42824;|&#42822;|&#42880;|[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780])/g,
  },
  {
    base: "LJ",
    letters: /(&#455;|[\u01C7])/g,
  },
  {
    base: "Lj",
    letters: /(&#456;|[\u01C8])/g,
  },
  {
    base: "M",
    letters:
      /(&#77;|&#9410;|&#65325;|&#7742;|&#7744;|&#7746;|&#11374;|&#412;|[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C])/g,
  },
  {
    base: "N",
    letters:
      /(&#78;|&#9411;|&#65326;|&#504;|&#323;|&#209;|&#7748;|&#327;|&#7750;|&#325;|&#7754;|&#7752;|&#544;|&#413;|&#42896;|&#42916;|&#330;|[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4\u014A])/g,
  },
  {
    base: "NJ",
    letters: /(&#458;|[\u01CA])/g,
  },
  {
    base: "Nj",
    letters: /(&#459;|[\u01CB])/g,
  },
  {
    base: "O",
    letters:
      /(&#79;|&#9412;|&#65327;|&#210;|&#211;|&#212;|&#7890;|&#7888;|&#7894;|&#7892;|&#213;|&#7756;|&#556;|&#7758;|&#332;|&#7760;|&#7762;|&#334;|&#558;|&#560;|&#214;|&#554;|&#7886;|&#336;|&#465;|&#524;|&#526;|&#416;|&#7900;|&#7898;|&#7904;|&#7902;|&#7906;|&#7884;|&#7896;|&#490;|&#492;|&#216;|&#510;|&#390;|&#415;|&#42826;|&#42828;|[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C])/g,
  },
  {
    base: "OE",
    letters: /(&#338;|[\u0152])/g,
  },
  {
    base: "OI",
    letters: /(&#418;|[\u01A2])/g,
  },
  {
    base: "OO",
    letters: /(&#42830;|[\uA74E])/g,
  },
  {
    base: "OU",
    letters: /(&#546;|[\u0222])/g,
  },
  {
    base: "P",
    letters:
      /(&#80;|&#9413;|&#65328;|&#7764;|&#7766;|&#420;|&#11363;|&#42832;|&#42834;|&#42836;|[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754])/g,
  },
  {
    base: "Q",
    letters:
      /(&#81;|&#9414;|&#65329;|&#42838;|&#42840;|&#586;|[\u0051\u24C6\uFF31\uA756\uA758\u024A])/g,
  },
  {
    base: "R",
    letters:
      /(&#82;|&#9415;|&#65330;|&#340;|&#7768;|&#344;|&#528;|&#530;|&#7770;|&#7772;|&#342;|&#7774;|&#588;|&#11364;|&#42842;|&#42918;|&#42882;|[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782])/g,
  },
  {
    base: "S",
    letters:
      /(&#83;|&#9416;|&#65331;|&#7838;|&#346;|&#7780;|&#348;|&#7776;|&#352;|&#7782;|&#7778;|&#7784;|&#536;|&#350;|&#11390;|&#42920;|&#42884;|[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784])/g,
  },
  {
    base: "T",
    letters:
      /(&#84;|&#9417;|&#65332;|&#7786;|&#356;|&#7788;|&#538;|&#354;|&#7792;|&#7790;|&#358;|&#428;|&#430;|&#574;|&#42886;|[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786])/g,
  },
  {
    base: "TH",
    letters: /(&#222;|[\u00DE])/g,
  },
  {
    base: "TZ",
    letters: /(&#42792;|[\uA728])/g,
  },
  {
    base: "U",
    letters:
      /(&#85;|&#9418;|&#65333;|&#217;|&#218;|&#219;|&#360;|&#7800;|&#362;|&#7802;|&#364;|&#220;|&#475;|&#471;|&#469;|&#473;|&#7910;|&#366;|&#368;|&#467;|&#532;|&#534;|&#431;|&#7914;|&#7912;|&#7918;|&#7916;|&#7920;|&#7908;|&#7794;|&#370;|&#7798;|&#7796;|&#580;|[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244])/g,
  },
  {
    base: "V",
    letters:
      /(&#86;|&#9419;|&#65334;|&#7804;|&#7806;|&#434;|&#42846;|&#581;|[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245])/g,
  },
  {
    base: "VY",
    letters: /(&#42848;|[\uA760])/g,
  },
  {
    base: "W",
    letters:
      /(&#87;|&#9420;|&#65335;|&#7808;|&#7810;|&#372;|&#7814;|&#7812;|&#7816;|&#11378;|[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72])/g,
  },
  {
    base: "X",
    letters:
      /(&#88;|&#9421;|&#65336;|&#7818;|&#7820;|[\u0058\u24CD\uFF38\u1E8A\u1E8C])/g,
  },
  {
    base: "Y",
    letters:
      /(&#89;|&#9422;|&#65337;|&#7922;|&#221;|&#374;|&#7928;|&#562;|&#7822;|&#376;|&#7926;|&#7924;|&#435;|&#590;|&#7934;|[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE])/g,
  },
  {
    base: "Z",
    letters:
      /(&#90;|&#9423;|&#65338;|&#377;|&#7824;|&#379;|&#381;|&#7826;|&#7828;|&#437;|&#548;|&#11391;|&#11371;|&#42850;|[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762])/g,
  },
  {
    base: "", //delete Niqqud in Hebrew
    letters: /[\u0591-\u05C7]/g,
  },
];

function removeDiacritics(str) {
  for (var i = 0; i < defaultDiacriticsRemovalMap.length; i++) {
    str = str.replace(
      defaultDiacriticsRemovalMap[i].letters,
      defaultDiacriticsRemovalMap[i].base
    );
  }
  return str;
}

function searchLanguage(firstLetter) {
  let codefirstLetter = firstLetter.charCodeAt();
  var codeLetter = [65, 90];
  if (codefirstLetter >= 65 && codefirstLetter <= 90) {
    // Latin
    return (codeLetter = [65, 90]);
  }
  if (codefirstLetter >= 1488 && codefirstLetter <= 1514) {
    // Hebrew
    return (codeLetter = [1488, 1514]);
  }
  if (codefirstLetter >= 913 && codefirstLetter <= 937) {
    // Greek
    return (codeLetter = [913, 929]); // 930 is blank
  }
  if (codefirstLetter >= 1040 && codefirstLetter <= 1071) {
    // Cyrillic
    return (codeLetter = [1040, 1071]);
  }
  if (codefirstLetter >= 1569 && codefirstLetter <= 1610) {
    // Arab
    return (codeLetter = [1569, 1594]);
  }
  if (codefirstLetter >= 19969 && codefirstLetter <= 40891) {
    // Chinese
    return (codeLetter = [19969, 40891]);
  }
  if (codefirstLetter >= 12354 && codefirstLetter <= 12436) {
    // Japan Hiragana
    return (codeLetter = [12388, 12418]);
  }
  console.log("Letter not detected : " + firstLetter + ":" + codefirstLetter);
  return codeLetter;
}

/**
 * The main function that mimics what your WordSearch constructor did:
 *  - Checks word lengths
 *  - Generates a matrix
 *  - Inserts words
 *  - Fills with random letters (if debug=false)
 * Returns the matrix plus the final wordsList
 */
function generatePuzzleData(settings) {
  // Reset the puzzleHasPolish flag for each new puzzle
  puzzleHasPolish = false;

  // 1) Normalize and validate words
  const wordsList = [];
  for (let i = 0; i < settings.words.length; i++) {
    wordsList[i] = settings.words[i].trim();

    // ---------- Polish Scenario Logic ----------
    // If this particular word has Polish diacritics, we skip removing them
    // and simply uppercase it. Also flag puzzleHasPolish = true.
    // Otherwise, we do the normal removeDiacritics->uppercase step.
    if (containsPolishCharacters(wordsList[i])) {
      puzzleHasPolish = true;
      settings.words[i] = wordsList[i].toUpperCase();
    } else {
      settings.words[i] = removeDiacritics(wordsList[i].toUpperCase());
    }
    // -------------------------------------------

    if (settings.words[i].length > settings.gridSize) {
      // If any word is longer than the entire grid dimension, bail out
      console.error(`Word "${settings.words[i]}" too long for grid.`);
      return { matrix: [], wordsList: [] };
    }
  }

  // 2) Create empty matrix
  const matrix = initMatrix(settings.gridSize);

  // 3) Sort words by length (longest first) – helps placement
  settings.words.sort((a, b) => b.length - a.length);
  // 4) Place all words via backtracking
  const allPlaced = placeAllWordsBacktracking(
    matrix,
    settings.words,
    settings.directions,
    settings.gridSize,
    0
  );
  if (!allPlaced) {
    console.error("Unable to place all words - no valid arrangement found.");
    return { matrix: [], wordsList: [] };
  }

  // 5) Fill remaining spots with random letters if not debugging
  if (!settings.debug) {
    fillUpFools(matrix, settings.words[0], settings.gridSize);
  }

  // 6) Return final puzzle
  return { matrix, wordsList };
}

// ---------- Helper Functions (migrated from the original) ---------- //

function initMatrix(size) {
  const matrix = [];
  for (let r = 0; r < size; r++) {
    matrix[r] = [];
    for (let c = 0; c < size; c++) {
      matrix[r][c] = { letter: ".", row: r, col: c };
    }
  }
  return matrix;
}

function placeAllWordsBacktracking(matrix, words, directions, gridSize, i) {
  if (i >= words.length) {
    return true;
  }
  const word = words[i];

  const shuffledDirections = [...directions];
  // Simple shuffle
  for (let j = shuffledDirections.length - 1; j > 0; j--) {
    const k = Math.floor(Math.random() * (j + 1));
    [shuffledDirections[j], shuffledDirections[k]] = [
      shuffledDirections[k],
      shuffledDirections[j],
    ];
  }

  // Try each direction systematically
  for (const direction of shuffledDirections) {
    const startPositions = computeValidStartPositions(
      word,
      direction,
      gridSize
    );

    for (const [startRow, startCol] of startPositions) {
      const canPlace = canPlaceWord(
        matrix,
        word,
        direction,
        startRow,
        startCol
      );
      if (canPlace) {
        placeWord(matrix, word, direction, startRow, startCol);
        // Recurse
        if (
          placeAllWordsBacktracking(matrix, words, directions, gridSize, i + 1)
        ) {
          return true;
        }
        // Otherwise, backtrack
        removeWord(matrix, word, direction, startRow, startCol);
      }
    }
  }
  return false;
}

function computeValidStartPositions(word, direction, gridSize) {
  const positions = [];
  const length = word.length;
  switch (direction) {
    case "W": // Horizontal: left-to-right
      for (let r = 0; r < gridSize; r++) {
        for (let c = 0; c <= gridSize - length; c++) {
          positions.push([r, c]);
        }
      }
      break;
    case "N": // Vertical: top-to-bottom
      for (let r = 0; r <= gridSize - length; r++) {
        for (let c = 0; c < gridSize; c++) {
          positions.push([r, c]);
        }
      }
      break;
    case "WN": // Diagonal: top-left to bottom-right
      for (let r = 0; r <= gridSize - length; r++) {
        for (let c = 0; c <= gridSize - length; c++) {
          positions.push([r, c]);
        }
      }
      break;
    case "EN": // Diagonal: top-right to bottom-left
      for (let r = 0; r <= gridSize - length; r++) {
        for (let c = length - 1; c < gridSize; c++) {
          positions.push([r, c]);
        }
      }
      break;
    default:
      break;
  }
  return positions;
}

function canPlaceWord(matrix, word, direction, row, col) {
  const [dr, dc] = directionsMap[direction];
  for (let i = 0; i < word.length; i++) {
    const r = row + i * dr;
    const c = col + i * dc;
    if (matrix[r][c].letter !== "." && matrix[r][c].letter !== word[i]) {
      return false;
    }
  }
  return true;
}

function placeWord(matrix, word, direction, row, col) {
  const [dr, dc] = directionsMap[direction];
  for (let i = 0; i < word.length; i++) {
    const r = row + i * dr;
    const c = col + i * dc;
    matrix[r][c].letter = word[i];
  }
}

function removeWord(matrix, word, direction, row, col) {
  const [dr, dc] = directionsMap[direction];
  for (let i = 0; i < word.length; i++) {
    const r = row + i * dr;
    const c = col + i * dc;
    if (matrix[r][c].letter === word[i]) {
      matrix[r][c].letter = ".";
    }
  }
}

// Fill remaining squares with random letters
function fillUpFools(matrix, firstWord, gridSize) {
  if (!firstWord) return;

  // If any puzzle word contained Polish diacritics,
  // fill empty cells with random letters from the Polish alphabet.
  if (puzzleHasPolish) {
    for (let r = 0; r < gridSize; r++) {
      for (let c = 0; c < gridSize; c++) {
        if (matrix[r][c].letter === ".") {
          const randomIndex = Math.rangeInt(0, POLISH_ALPHABET.length - 1);
          matrix[r][c].letter = POLISH_ALPHABET[randomIndex];
        }
      }
    }
  } else {
    // Otherwise, original approach using searchLanguage for the entire puzzle
    const rangeLanguage = searchLanguage(firstWord[0]);
    for (let r = 0; r < gridSize; r++) {
      for (let c = 0; c < gridSize; c++) {
        if (matrix[r][c].letter === ".") {
          matrix[r][c].letter = String.fromCharCode(
            Math.rangeInt(rangeLanguage[0], rangeLanguage[1])
          );
        }
      }
    }
  }
}
