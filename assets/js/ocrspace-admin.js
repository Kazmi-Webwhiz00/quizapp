jQuery(function ($) {
  const sel = {
    input: "#kw_pdf_upload_input",
    btn: "#kw_pdf_upload_btn",
    results: "#kw_ocrspace-results",
  };

  // Set the worker source for pdf.js (if not already set in another script)
  if (typeof pdfjsLib !== "undefined") {
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.379/pdf.worker.min.mjs";
  } else {
    console.error(
      "PDF.js library not loaded. Make sure it's enqueued correctly."
    );
  }

  // Function to initialize Tesseract worker once
  let tesseractWorker = null;
  var type = "prompt"; // Default type for generation

  // Dropdown functionality
  const aiContainer = document.querySelector(".kw_ai-generate-container");
  const aiOptions = document.querySelectorAll(".kw_ai-option");
  const generateBtn = document.querySelector("#kw_generate-question-btn");
  const dropdown = document.querySelector(".kw_ai-dropdown");

  window.uploadedImages = [];
  window.uploadedPDFs = [];
  window.numberOfPdfs = 0; // Track number of PDFs uploaded
  window.numberOfImages = 0; // Track number of images uploaded
  window.textBoxContent = ""; // Store text box content globally
  window.processingFiles = new Set();
  window.fileIdMap = new Map();
  window.ocrTexts = { images: {}, pdfs: {} };
  const MAX_IMAGES = 4;

  // File upload handlers function
  function setupFileUploadHandlers() {
    // Delegate clicks on the “Choose images” button
    $(document).on("click", "#kw_image_upload_btn", function () {
      $("#kw_image_upload_input").click();
    });

    // PDFs likewise
    $(document).on("click", "#kw_pdf_upload_btn", function () {
      $("#kw_pdf_upload_input").click();
    });
  }

  // Add image file with preview
  function addImageFile(file, previewContainer, labelElement) {
    // unique ID
    const fileId = `${Date.now()}-${Math.random()}`;
    window.processingFiles.add(fileId);
    window.fileIdMap.set(file.name, fileId);

    // read once
    const reader = new FileReader();
    reader.onload = ({ target: { result } }) => {
      // build item
      const item = document.createElement("div");
      item.className = "file-item";
      item.dataset.fileId = fileId;
      item.innerHTML = `
        <img src="${result}" alt="${file.name}">
        <div class="file-name" title="${file.name}">${file.name}</div>
        <button class="file-remove">×</button>
        <div class="processing-overlay" id="processing-${fileId}">
          <svg class="progress-ring" width="70" height="70">
            <circle class="progress-ring-circle" cx="35" cy="35" r="30"></circle>
          </svg>
        </div>
      `;

      console.log("Adding image file:", previewContainer);
      // append & wire remove
      previewContainer.appendChild(item);
      item
        .querySelector(".file-remove")
        .addEventListener("click", () => removeFile(fileId, "image"));

      console.log("::Image file added:", file.name);

      // metadata
      window.uploadedImages.push({
        id: fileId,
        file,
        name: file.name,
        preview: result,
      });
      window.numberOfImages++;
      updateImageLabel(labelElement);

      console.log(
        "::Updated image label with count:",
        window.uploadedImages.length
      );
      // done processing after 2–4s
      setTimeout(() => {
        const overlay = document.getElementById(`processing-${fileId}`);
        if (overlay) overlay.remove();
        window.processingFiles.delete(fileId);
      }, 4000);
    };

    reader.readAsDataURL(file);
  }

  // Add PDF file with preview
  function addPDFFile(file, previewContainer, labelElement) {
    console.log("Adding PDF file:", file.name);
    const fileId = Date.now() + Math.random();

    // Start processing
    processingFiles.add(fileId);
    window.fileIdMap.set(file.name, fileId);

    const fileItem = document.createElement("div");
    fileItem.className = "file-item";
    fileItem.dataset.fileId = fileId;

    fileItem.innerHTML = `
          <div class="file-icon">📄</div>
          <div class="file-name" title="${file.name}">${file.name}</div>
          <button class="file-remove">×</button>
          <div class="processing-overlay" id="processing-${fileId}">
              <svg class="progress-ring" width="70" height="70">
                  <circle class="progress-ring-circle" cx="35" cy="35" r="30"></circle>
              </svg>
          </div>
      `;

    previewContainer.appendChild(fileItem);

    // Attach click event listener on the remove button
    const removeBtn = fileItem.querySelector(".file-remove");
    removeBtn.addEventListener("click", () => {
      removeFile(fileId, "pdf");
    });

    // Add to uploaded PDFs array
    window.uploadedPDFs.push({
      id: fileId,
      file: file,
      name: file.name,
    });

    window.numberOfPdfs = window.numberOfPdfs + 1;

    updatePDFLabel(labelElement);

    // Simulate processing (2-4 seconds)
    setTimeout(() => {
      const processingOverlay = document.getElementById(`processing-${fileId}`);
      if (processingOverlay) {
        processingOverlay.remove();
      }
      processingFiles.delete(fileId);
    }, 2000 + Math.random() * 2000);
  }

  // Remove file function
  // Remove file function
  function removeFile(fileId, type) {
    const fileItem = document.querySelector(`[data-file-id="${fileId}"]`);
    if (fileItem) {
      fileItem.remove();
    }

    if (type === "image") {
      window.uploadedImages = window.uploadedImages.filter(
        (img) => img.id !== fileId
      );
      const imageLabel = document.getElementById("kw_image_upload_label");
      updateImageLabel(imageLabel);
      window.numberOfImages -= 1; // Update count of images
    } else if (type === "pdf") {
      window.uploadedPDFs = window.uploadedPDFs.filter(
        (pdf) => pdf.id !== fileId
      );
      const pdfLabel = document.getElementById("kw_pdf_upload_label");
      updatePDFLabel(pdfLabel);
      window.numberOfPdfs -= 1; // Decrement the count of PDFs
    }

    processingFiles.delete(fileId);
  }

  // Update image label
  function updateImageLabel(labelElement) {
    const count = window.uploadedImages.length;
    if (count === 0) {
      labelElement.innerHTML = "No images chosen";
    } else if (count === 1) {
      labelElement.innerHTML = `1 image chosen <span class="upload-counter">(${count})</span>`;
    } else {
      labelElement.innerHTML = `${count} images chosen <span class="upload-counter">(${count})</span>`;
    }
  }

  // Update PDF label
  function updatePDFLabel(labelElement) {
    // console.log("Updating PDF label with count:", uploadedPDFs.length);
    const count = window.uploadedPDFs.length;
    if (count === 0) {
      labelElement.innerHTML = "No PDFs chosen";
    } else if (count === 1) {
      labelElement.innerHTML = `1 PDF chosen <span class="upload-counter">(${count})</span>`;
    } else {
      labelElement.innerHTML = `${count} PDFs chosen <span class="upload-counter">(${count})</span>`;
    }
  }

  generateBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    aiContainer.classList.toggle("active");
  });

  aiOptions.forEach((option) => {
    option.addEventListener("click", (e) => {
      type = e.currentTarget.dataset.type;
      console.log("Selected generation type:", type);
      aiContainer.classList.remove("active");
      // Here you would trigger the specific generation method
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", () => {
    aiContainer.classList.remove("active");
  });

  $(sel.btn).on("click", function () {
    $(sel.input).trigger("click");
  });

  // 32 MB hard limit per file for OpenAI
  const MAX_OPENAI_FILE_BYTES = 32 * 1024 * 1024; // 32 MB in bytes

  $(document)
    .off("change", "#kw_pdf_upload_input")
    .on("change", "#kw_pdf_upload_input", async function (e) {
      const pdfsPreview = document.getElementById("kw_pdfs_preview");
      const pdfUploadLabel = document.getElementById("kw_pdf_upload_label");
      // extractPdfText(e.target.files[0]);
      const filesChosen = Array.from(e.target.files).filter(
        (f) => f.type === "application/pdf"
      );

      // 0) Enforce only one PDF in total
      if (window.numberOfPdfs + filesChosen.length > 1) {
        // Swal.fire({
        //   icon: "error",
        //   title: "Only one PDF allowed",
        //   text: "Please remove your existing PDF before uploading a new one.",
        // });
        alert(OCRSPACE.onlyOnePdfText);
        e.target.value = "";
        return;
      }

      const allFiles = Array.from(e.target.files).filter(
        (f) => f.type === "application/pdf"
      );
      console.log("::allFiles", allFiles);
      if (allFiles.length === 0) return;

      // 1) Per-file check
      console.log("::Checking file sizes", MAX_OPENAI_FILE_BYTES);
      const tooBig = allFiles.filter((f) => {
        console.log("::file size", f.size);
        return f.size > MAX_OPENAI_FILE_BYTES;
      });
      console.log("::tooBig", tooBig);
      if (tooBig.length) {
        const list = tooBig
          .map((f) => `${f.name} (${(f.size / 1048576).toFixed(1)} MB)`)
          .join("<br>");
        Swal.fire({
          icon: "error",
          title: "PDF too large",
          html: `Each PDF must be under 32 MB.<br><br>The following were rejected:<br>${list}`,
        });
        e.target.value = ""; // reset file input
        return; // STOP here
      }

      // 2) Combined-size check (OpenAI can also reject total batch)
      const totalBytes = allFiles.reduce((sum, f) => sum + f.size, 0);
      if (totalBytes > MAX_OPENAI_FILE_BYTES) {
        Swal.fire({
          icon: "error",
          title: "Total size exceeds 32 MB",
          text: "Please upload fewer/smaller PDFs.",
        });
        e.target.value = "";
        return; // STOP here
      }

      // 3) Now split large PDFs into ≤ 100-page chunks
      for (const file of allFiles) {
        console.log("::Processing PDF:", file.name);

        // 1) Read the file into an ArrayBuffer
        const arrayBuffer = await file.arrayBuffer();

        // 2) Load with PDF.js
        const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
        const pdf = await loadingTask.promise;
        const pageCount = pdf.numPages; // <-- use PDF.js’s numPages

        // 3) If ≤ 100 pages, no split needed
        if (pageCount <= 100) {
          console.log("::No split needed:", file.name);
          addPDFFile(file, pdfsPreview, pdfUploadLabel);
          continue;
        }

        // Otherwise split into 100-page blobs
        // for (let i = 0; i < pageCount; i += 100) {
        //   const chunkPdf = await PDFDocument.create();
        //   const end = Math.min(i + 100, pageCount);
        //   const pages = await chunkPdf.copyPages(
        //     origPdf,
        //     Array.from({ length: end - i }, (_, k) => i + k)
        //   );
        //   pages.forEach((p) => chunkPdf.addPage(p));

        //   const chunkBytes = await chunkPdf.save();
        //   const chunkBlob = new Blob([chunkBytes], { type: "application/pdf" });
        //   const chunkName = `${file.name.replace(/\.pdf$/i, "")}_part${
        //     i / 100 + 1
        //   }.pdf`;

        //   // Preserve original behavior
        //   Object.defineProperty(chunkBlob, "name", { value: chunkName });
        //   // window.uploadedPDFs.push(chunkBlob);
        //   addPDFFile(chunkBlob, pdfsPreview, pdfUploadLabel);
        // }
      }
    });

  // When user clicks the styled button, open file selector
  $("#kw_image_upload_btn").on("click", function () {
    $("#kw_image_upload_input").trigger("click");
  });

  // When files are selected, upload them via AJAX
  // When files selected
  // Initialize the uploadedImages array globally if it doesn't exist yet

  $(document).on("change", "#kw_image_upload_input", function (e) {
    const previewContainer = document.getElementById("kw_images_preview");
    const labelElement = document.getElementById("kw_image_upload_label");
    const files = Array.from(e.target.files || []);
    if (!files.length) return;

    // Filter once
    const imageFiles = files.filter((f) => f.type.startsWith("image/"));
    if (!imageFiles.length) return;

    // Enforce limit
    if (window.numberOfImages + imageFiles.length > MAX_IMAGES) {
      alert(OCRSPACE.onlyFourImagesText);
      e.target.value = "";
      return;
    }

    // Process only image files
    imageFiles.forEach((file) =>
      addImageFile(file, previewContainer, labelElement)
    );

    // reset if desired
    e.target.value = "";
  });

  // Helper: convert Image or PDF page Blob/File to base64 string
  // ---------- CONFIG ----------
  // const OPENAI_API_KEY = OCRSPACE.apiKey;

  // ---------- HELPERS ----------
  function blobToDataURL(file) {
    return new Promise((resolve, reject) => {
      if (!(file instanceof Blob)) return reject(new Error("Not a Blob/File"));
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result); // full data:... URL
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  // Upload a PDF (or any doc) once, get file_id back (preferred for PDFs)
  // async function uploadToOpenAI(file) {
  //   console.log("Uploading file to OpenAI:", file.name);
  //   const form = new FormData();
  //   const fileData =
  //     file instanceof File
  //       ? file
  //       : new File([file], "document.pdf", { type: "application/pdf" });
  //   form.append("file", fileData, fileData.name);
  //   form.append("purpose", "assistants");

  //   // file =
  //   //   file instanceof File
  //   //     ? file
  //   //     : new File([file], "document.pdf", { type: "application/pdf" });
  //   // form.append("file", file, file.name);

  //   const res = await fetch("https://api.openai.com/v1/files", {
  //     method: "POST",
  //     headers: { Authorization: `Bearer ${OPENAI_API_KEY}` },
  //     body: form,
  //   });
  //   console.log("::upload response", res);
  //   if (!res.ok) throw new Error(await res.text());
  //   const json = await res.json();
  //   console.log("::upload result", json);
  //   return json.id; // file_id
  // }

  // Build the multimodal message for the Responses API
  // Builds the multimodal parts (text + images + pdfs) for chat/completions
  async function prepareMultimodalContent(promptText) {
    console.log("Preparing multimodal content for prompt:", promptText);
    const parts = [{ type: "text", text: promptText }];

    // ---- IMAGES => data URLs ----
    if (window.uploadedImages.length > 0) {
      for (const item of window.uploadedImages || []) {
        const blob = item.file instanceof Blob ? item.file : item;
        if (!blob.type.startsWith("image/")) continue;

        const dataUrl = await blobToDataURL(blob);
        parts.push({
          type: "image_url",
          image_url: {
            url: dataUrl,
            detail: "high", // or "high" for better quality
          },
        });
      }
    }

    // if (window.uploadedPDFs.length > 0) {
    //   for (const pdf of window.uploadedPDFs || []) {
    //     const blob = pdf.file instanceof Blob ? pdf.file : pdf;
    //     console.log("::Processing PDF for upload:", blob.name, pdf.file);
    //     if (blob.type === "application/pdf") {
    //       if (blob.size > 32 * 1024 * 1024) {
    //         console.warn(`${blob.name} >32 MB ‑ skipped`);
    //         continue;
    //       }
    //       const fileId = await uploadToOpenAI(blob); // your helper
    //       parts.push({ type: "file", file: { file_id: fileId } });
    //     }
    //   }
    // }

    // ---- PDFs: Extract text using the new function ----
    if (window.uploadedPDFs && window.uploadedPDFs.length > 0) {
      for (const pdf of window.uploadedPDFs) {
        const blob = pdf.file instanceof Blob ? pdf.file : pdf;
        console.log("::Processing PDF for text extraction:", blob.name);
        if (blob.type === "application/pdf") {
          if (blob.size > 32 * 1024 * 1024) {
            // Still apply size warning
            console.warn(
              `${
                blob.name || "document.pdf"
              } >32 MB ‑ skipped for text extraction.`
            );
            continue;
          }
          // Extract text from the PDF Blob
          const extractedPdfText = await extractPdfTextFromBlob(blob);
          if (extractedPdfText) {
            parts.push({
              type: "text",
              text: `Content from PDF: ${extractedPdfText}`,
            });
          } else {
            console.warn(
              `No text extracted from PDF: ${blob.name || "document.pdf"}`
            );
          }
        }
      }
    }

    processTextInput(type, parts);
    console.log("Prepared multimodal content:", parts);
    return parts;
  }

  function processTextInput(type, parts = []) {
    if (type !== "text") return; // Early return if not text type
    console.log("Processing text input for multimodal parts...");

    const textContent = window.sourceText;
    if (!textContent) {
      if (typeof Swal !== "undefined") {
        Swal.showValidationMessage("Please enter some text.");
      } else {
        alert("Please enter some text."); // Fallback if SweetAlert is unavailable
      }
      return false;
    }

    // Debugging log (optional, can be removed in production)
    // console.debug("Adding text content to multimodal parts:", textContent);

    console.log("Adding text content to multimodal parts:", textContent);
    parts.push({
      type: "text",
      text: textContent,
    });

    return;
  }

  async function getTesseractWorker() {
    if (tesseractWorker) {
      return tesseractWorker;
    }
    console.log("Initializing Tesseract.js worker...");
    tesseractWorker = await Tesseract.createWorker("eng", 1, {
      logger: (m) => console.log(m),
    });
    console.log("Tesseract.js worker initialized.");
    return tesseractWorker;
  }

  async function terminateTesseractWorker() {
    if (tesseractWorker) {
      console.log("Terminating Tesseract.js worker...");
      await tesseractWorker.terminate();
      tesseractWorker = null;
      console.log("Tesseract.js worker terminated.");
    }
  }

  async function extractPdfTextFromBlob(pdfBlob) {
    console.log(
      "Extracting text from PDF Blob:",
      pdfBlob.name || "document.pdf"
    );

    // 1️⃣ Convert Blob to a URL for pdf.js to load
    const url = URL.createObjectURL(pdfBlob);

    // 2️⃣ Load the PDF document
    const loadingTask = pdfjsLib.getDocument({ url: url });
    const pdf = await loadingTask.promise;

    let fullText = "";
    const minTextLengthForNative = 50; // Threshold to decide if native extraction was enough

    // 3️⃣ Iterate pages and attempt native text extraction first
    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i);
      const content = await page.getTextContent();
      const strings = content.items.map((item) => item.str);
      let pageText = strings.join(" ").trim();

      // If native extraction yielded very little or no text,
      // assume it's an image-based PDF and use Tesseract.js
      if (
        pageText.length < minTextLengthForNative &&
        typeof Tesseract !== "undefined" // Ensure Tesseract is loaded
      ) {
        console.warn(
          `Page ${i} has limited native text. Attempting OCR with Tesseract.js...`
        );

        // Render page to canvas
        const viewport = page.getViewport({ scale: 2 }); // Scale can impact OCR accuracy and performance
        const canvas = document.createElement("canvas");
        const context = canvas.getContext("2d");
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
          canvasContext: context,
          viewport: viewport,
        };
        await page.render(renderContext).promise;

        // Get Tesseract worker (will initialize if not already)
        const worker = await getTesseractWorker();

        // Perform OCR on the canvas image
        const {
          data: { text },
        } = await worker.recognize(canvas);
        pageText = text.trim();

        canvas.remove(); // Clean up the canvas element
      }

      fullText += pageText + "\n\n"; // Add extracted text for this page
      console.log("Extracted text from page:", fullText);
    }

    // Clean up the Blob URL
    URL.revokeObjectURL(url);

    return fullText.trim();
  }

  function toggleSettingsCard(header) {
    const card = header.closest(".kw_settings-card");
    // const isCollapsed = card.hasClass("collapsed");
    card.classList.toggle("collapsed");
  }

  document.querySelectorAll(".kw_settings-header").forEach((header) => {
    header.addEventListener("click", () => {
      toggleSettingsCard(header);
    });
  });

  // Expose setupFileUploadHandlers for use in your Swal calls
  window.setupFileUploadHandlers = setupFileUploadHandlers;
  window.blobToDataURL = blobToDataURL;
  window.prepareMultimodalContent = prepareMultimodalContent;
  window.terminateTesseractWorker = terminateTesseractWorker;

  // async function testPdfWithO4Mini(userPrompt) {
  //   const API_KEY = OCRSPACE.apiKey; // Make sure OCRSPACE.apiKey is defined elsewhere

  //   try {
  //     // If you want to upload a PDF file, uncomment and adapt this block:
  //     /*
  //     const uploadRes = await fetch("https://api.openai.com/v1/files", {
  //       method: "POST",
  //       headers: { Authorization: `Bearer ${API_KEY}` },
  //       body: (() => {
  //         const fd = new FormData();
  //         fd.append("file", pdfFile); // pdfFile must be defined and be a File object
  //         fd.append("purpose", "user_data");
  //         return fd;
  //       })(),
  //     });
  //     if (!uploadRes.ok) {
  //       throw new Error(`Upload failed: ${await uploadRes.text()}`);
  //     }
  //     const { id: fileId } = await uploadRes.json();
  //     console.log("Uploaded. file_id =", fileId);
  //     */

  //     // Build the multimodal payload with userPrompt (and optionally a file_id if you uploaded a PDF)
  //     const payload = {
  //       model: "o4-mini",
  //       input: [
  //         {
  //           role: "user",
  //           content: [
  //             {
  //               type: "input_text",
  //               text: userPrompt,
  //             },
  //             // Uncomment and add file_id if you uploaded a PDF
  //             // { type: "input_file", file_id: fileId },
  //           ],
  //         },
  //       ],
  //       max_output_tokens: 300,
  //       store: false,
  //     };

  //     console.log("Requesting /v1/responses with PDF...");
  //     const resp = await fetch("https://api.openai.com/v1/responses", {
  //       method: "POST",
  //       headers: {
  //         Authorization: `Bearer ${API_KEY}`,
  //         "Content-Type": "application/json",
  //       },
  //       body: JSON.stringify(payload),
  //     });

  //     if (!resp.ok) {
  //       throw new Error(`Responses API error: ${await resp.text()}`);
  //     }

  //     const data = await resp.json();
  //     console.log("Full API response:", data);

  //     // Extract the text output
  //     const text =
  //       data.output_text ||
  //       data.output
  //         ?.flatMap((b) => b.content?.map((c) => c.text || "") || [])
  //         .join("") ||
  //       "";
  //     console.log("Model output text:", text.trim());

  //     return text.trim(); // Return the output text for further use
  //   } catch (err) {
  //     console.error("Error in testPdfWithO4Mini:", err);
  //     throw err; // Re-throw so calling code can catch it if needed
  //   }
  // }
});
