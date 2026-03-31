<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
header('Content-Type: application/javascript; charset=UTF-8');
?>
/**
 * MokoDoliDymo Label Designer
 *
 * DOM-based visual label editor with drag-and-drop positioning,
 * resize handles, data binding, and image support.
 * Uses safe DOM construction (no innerHTML with user content).
 */
var MDD = (function() {
	'use strict';

	var SCALE = 3; // pixels per mm
	var canvas, propsPanel, data;
	var layout = { elements: [] };
	var selectedId = null;
	var elemIdCounter = 0;

	function init(serverData) {
		data = serverData;
		canvas = document.getElementById('mdd-canvas');
		propsPanel = document.getElementById('mdd-props-content');
		layout = data.layout && data.layout.elements ? data.layout : { elements: [] };

		canvas.style.width = (data.labelWidth * SCALE) + 'px';
		canvas.style.height = (data.labelHeight * SCALE) + 'px';

		// Determine next element ID
		(layout.elements || []).forEach(function(el) {
			var num = parseInt((el.id || '').replace('elem_', ''), 10);
			if (num >= elemIdCounter) elemIdCounter = num + 1;
		});

		renderAll();

		canvas.addEventListener('mousedown', function(e) {
			if (e.target === canvas) selectElement(null);
		});

		// Toolbar buttons
		document.getElementById('btn-add-text').addEventListener('click', function() { addElement('text'); });
		document.getElementById('btn-add-barcode').addEventListener('click', function() { addElement('barcode'); });
		document.getElementById('btn-add-qrcode').addEventListener('click', function() { addElement('qrcode'); });
		document.getElementById('btn-add-image').addEventListener('click', function() { addElement('image'); });
		document.getElementById('btn-add-line').addEventListener('click', function() { addElement('line'); });
		document.getElementById('btn-duplicate').addEventListener('click', duplicateSelected);
		document.getElementById('btn-delete').addEventListener('click', deleteSelected);
		document.getElementById('btn-save').addEventListener('click', save);

		// Keyboard shortcuts
		document.addEventListener('keydown', function(e) {
			if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
			if (e.key === 'Delete' || e.key === 'Backspace') { deleteSelected(); e.preventDefault(); }
			if (e.key === 'd' && (e.ctrlKey || e.metaKey)) { duplicateSelected(); e.preventDefault(); }
			if (e.key === 's' && (e.ctrlKey || e.metaKey)) { save(); e.preventDefault(); }
		});
	}

	// ── Rendering ──────────────────────────────────────────────

	function renderAll() {
		while (canvas.firstChild) canvas.removeChild(canvas.firstChild);
		(layout.elements || []).forEach(renderElement);
	}

	function renderElement(el) {
		var div = document.createElement('div');
		div.className = 'mdd-elem';
		div.dataset.id = el.id;
		div.style.left = (el.x * SCALE) + 'px';
		div.style.top = (el.y * SCALE) + 'px';
		div.style.width = (el.width * SCALE) + 'px';
		div.style.height = (el.height * SCALE) + 'px';

		var props = el.properties || {};

		switch (el.type) {
			case 'text':
				div.classList.add('mdd-elem-text');
				div.textContent = props.binding ? '{' + props.binding + '}' : (props.text || 'Text');
				div.style.fontSize = ((props.fontSize || 12) * SCALE * 0.35) + 'px';
				div.style.fontWeight = props.fontWeight || 'normal';
				div.style.textAlign = props.textAlign || 'left';
				break;

			case 'barcode':
				div.classList.add('mdd-elem-barcode');
				if (props.showText !== false) {
					var bcSpan = document.createElement('span');
					bcSpan.textContent = props.binding ? '{' + props.binding + '}' : (props.data || '0000000');
					div.appendChild(bcSpan);
				}
				break;

			case 'qrcode':
				div.classList.add('mdd-elem-qrcode');
				div.textContent = 'QR';
				break;

			case 'image':
				div.classList.add('mdd-elem-image');
				if (props.src) {
					var img = document.createElement('img');
					img.src = props.src;
					img.alt = 'Label image';
					div.appendChild(img);
				} else {
					div.textContent = 'Image';
				}
				break;

			case 'line':
				div.classList.add('mdd-elem-line');
				if ((props.direction || 'horizontal') === 'horizontal') {
					div.style.height = ((props.thickness || 1) * SCALE) + 'px';
				} else {
					div.style.width = ((props.thickness || 1) * SCALE) + 'px';
				}
				div.style.backgroundColor = props.color || '#000';
				break;
		}

		// Resize handle
		var handle = document.createElement('div');
		handle.className = 'mdd-resize';
		div.appendChild(handle);

		// Drag and select events
		div.addEventListener('mousedown', function(e) {
			if (e.target === handle) {
				startResize(e, el);
			} else {
				startDrag(e, el);
			}
			selectElement(el.id);
			e.stopPropagation();
		});

		if (el.id === selectedId) div.classList.add('mdd-selected');
		canvas.appendChild(div);
	}

	// ── Drag & Resize ─────────────────────────────────────────

	function startDrag(e, el) {
		var sx = e.clientX, sy = e.clientY, ox = el.x, oy = el.y;
		function onMove(e2) {
			el.x = Math.max(0, Math.round((ox + (e2.clientX - sx) / SCALE) * 10) / 10);
			el.y = Math.max(0, Math.round((oy + (e2.clientY - sy) / SCALE) * 10) / 10);
			renderAll();
		}
		function onUp() {
			document.removeEventListener('mousemove', onMove);
			document.removeEventListener('mouseup', onUp);
			updateProps();
		}
		document.addEventListener('mousemove', onMove);
		document.addEventListener('mouseup', onUp);
	}

	function startResize(e, el) {
		var sx = e.clientX, sy = e.clientY, ow = el.width, oh = el.height;
		function onMove(e2) {
			el.width = Math.max(2, Math.round((ow + (e2.clientX - sx) / SCALE) * 10) / 10);
			el.height = Math.max(2, Math.round((oh + (e2.clientY - sy) / SCALE) * 10) / 10);
			renderAll();
		}
		function onUp() {
			document.removeEventListener('mousemove', onMove);
			document.removeEventListener('mouseup', onUp);
			updateProps();
		}
		document.addEventListener('mousemove', onMove);
		document.addEventListener('mouseup', onUp);
		e.preventDefault();
	}

	// ── Selection & Properties ─────────────────────────────────

	function selectElement(id) {
		selectedId = id;
		renderAll();
		updateProps();
	}

	function getSelected() {
		if (!selectedId) return null;
		return (layout.elements || []).find(function(el) { return el.id === selectedId; });
	}

	/**
	 * Build the properties panel using safe DOM construction.
	 * All text values are set via textContent or value (never innerHTML with user data).
	 */
	function updateProps() {
		var el = getSelected();

		// Clear panel safely
		while (propsPanel.firstChild) propsPanel.removeChild(propsPanel.firstChild);

		if (!el) {
			var hint = document.createElement('p');
			hint.className = 'opacitymedium';
			hint.textContent = 'Select an element to edit its properties.';
			propsPanel.appendChild(hint);
			return;
		}

		var props = el.properties || {};

		// Position row
		appendPropRow(propsPanel, [
			{ label: 'X (mm)', type: 'number', value: el.x, step: '0.1', onChange: function(v) { setProp('x', v); } },
			{ label: 'Y (mm)', type: 'number', value: el.y, step: '0.1', onChange: function(v) { setProp('y', v); } }
		]);
		appendPropRow(propsPanel, [
			{ label: 'Width', type: 'number', value: el.width, step: '0.1', onChange: function(v) { setProp('width', v); } },
			{ label: 'Height', type: 'number', value: el.height, step: '0.1', onChange: function(v) { setProp('height', v); } }
		]);

		// Type-specific properties
		switch (el.type) {
			case 'text':
				appendBindingSelect(propsPanel, props.binding || '');
				appendInput(propsPanel, 'Static Text', 'text', props.text || '', function(v) { setSubProp('text', v); });
				appendInput(propsPanel, 'Font Size', 'number', props.fontSize || 12, function(v) { setSubProp('fontSize', parseInt(v, 10)); }, '4', '120');
				appendSelect(propsPanel, 'Font Weight', [['normal','Normal'],['bold','Bold']], props.fontWeight || 'normal', function(v) { setSubProp('fontWeight', v); });
				appendSelect(propsPanel, 'Alignment', [['left','Left'],['center','Center'],['right','Right']], props.textAlign || 'left', function(v) { setSubProp('textAlign', v); });
				break;

			case 'barcode':
				appendBindingSelect(propsPanel, props.binding || '');
				appendInput(propsPanel, 'Static Data', 'text', props.data || '', function(v) { setSubProp('data', v); });
				appendSelect(propsPanel, 'Format', [['CODE128','CODE128'],['EAN13','EAN13'],['EAN8','EAN8'],['UPCA','UPCA'],['CODE39','CODE39'],['ITF14','ITF14']], props.format || 'CODE128', function(v) { setSubProp('format', v); });
				appendCheckbox(propsPanel, 'Show text below', props.showText !== false, function(v) { setSubProp('showText', v); });
				break;

			case 'qrcode':
				appendBindingSelect(propsPanel, props.binding || '');
				appendInput(propsPanel, 'Static Data', 'text', props.data || '', function(v) { setSubProp('data', v); });
				break;

			case 'image':
				appendFileInput(propsPanel, 'Image File', function(dataUrl) {
					setSubProp('src', dataUrl);
				});
				if (props.src) {
					var preview = document.createElement('img');
					preview.src = props.src;
					preview.style.cssText = 'max-width:100%;max-height:60px;border:1px solid #ccc;margin-top:4px';
					preview.alt = 'Preview';
					propsPanel.appendChild(preview);
				}
				appendSelect(propsPanel, 'Fit Mode', [['contain','Contain'],['cover','Cover'],['fill','Fill']], props.fit || 'contain', function(v) { setSubProp('fit', v); });
				break;

			case 'line':
				appendSelect(propsPanel, 'Direction', [['horizontal','Horizontal'],['vertical','Vertical']], props.direction || 'horizontal', function(v) { setSubProp('direction', v); });
				appendInput(propsPanel, 'Thickness (mm)', 'number', props.thickness || 1, function(v) { setSubProp('thickness', parseFloat(v)); }, '0.5', '10', '0.5');
				appendColorInput(propsPanel, 'Color', props.color || '#000000', function(v) { setSubProp('color', v); });
				break;
		}
	}

	// ── DOM builder helpers (safe, no innerHTML) ───────────────

	function appendLabel(parent, text) {
		var lbl = document.createElement('label');
		lbl.textContent = text;
		parent.appendChild(lbl);
	}

	function appendInput(parent, labelText, type, value, onChange, min, max, step) {
		appendLabel(parent, labelText);
		var inp = document.createElement('input');
		inp.type = type;
		inp.value = value;
		if (min !== undefined) inp.min = min;
		if (max !== undefined) inp.max = max;
		if (step !== undefined) inp.step = step;
		inp.addEventListener('change', function() { onChange(this.value); });
		parent.appendChild(inp);
	}

	function appendSelect(parent, labelText, options, current, onChange) {
		appendLabel(parent, labelText);
		var sel = document.createElement('select');
		options.forEach(function(opt) {
			var o = document.createElement('option');
			o.value = opt[0];
			o.textContent = opt[1];
			if (opt[0] === current) o.selected = true;
			sel.appendChild(o);
		});
		sel.addEventListener('change', function() { onChange(this.value); });
		parent.appendChild(sel);
	}

	function appendCheckbox(parent, labelText, checked, onChange) {
		var lbl = document.createElement('label');
		var cb = document.createElement('input');
		cb.type = 'checkbox';
		cb.checked = checked;
		cb.addEventListener('change', function() { onChange(this.checked); });
		lbl.appendChild(cb);
		lbl.appendChild(document.createTextNode(' ' + labelText));
		parent.appendChild(lbl);
	}

	function appendColorInput(parent, labelText, value, onChange) {
		appendLabel(parent, labelText);
		var inp = document.createElement('input');
		inp.type = 'color';
		inp.value = value;
		inp.addEventListener('change', function() { onChange(this.value); });
		parent.appendChild(inp);
	}

	function appendFileInput(parent, labelText, onLoad) {
		appendLabel(parent, labelText);
		var inp = document.createElement('input');
		inp.type = 'file';
		inp.accept = 'image/*';
		inp.addEventListener('change', function() {
			if (!this.files || !this.files[0]) return;
			var reader = new FileReader();
			reader.onload = function(e) { onLoad(e.target.result); };
			reader.readAsDataURL(this.files[0]);
		});
		parent.appendChild(inp);
	}

	function appendBindingSelect(parent, current) {
		var options = [['', '-- None (static) --']];
		var fields = data.bindableFields || {};
		for (var key in fields) {
			if (fields.hasOwnProperty(key)) {
				options.push([key, fields[key] + ' (' + key + ')']);
			}
		}
		appendSelect(parent, 'Data Binding', options, current, function(v) { setSubProp('binding', v); });
	}

	function appendPropRow(parent, items) {
		var row = document.createElement('div');
		row.className = 'mdd-prop-row';
		items.forEach(function(item) {
			var col = document.createElement('div');
			appendInput(col, item.label, item.type, item.value, item.onChange, undefined, undefined, item.step);
			row.appendChild(col);
		});
		parent.appendChild(row);
	}

	// ── Element CRUD ──────────────────────────────────────────

	function addElement(type) {
		elemIdCounter++;
		var defaults = {
			text:    { text: 'Text', fontSize: 12, fontWeight: 'normal', textAlign: 'left', binding: '' },
			barcode: { data: '0000000000', format: 'CODE128', showText: true, binding: 'product.barcode' },
			qrcode:  { data: '', binding: '' },
			image:   { src: '', fit: 'contain' },
			line:    { direction: 'horizontal', thickness: 1, color: '#000000' }
		};
		var sizes = { text: [30,10], barcode: [35,12], qrcode: [15,15], image: [20,15], line: [40,1] };
		var sz = sizes[type] || [30,10];

		var el = {
			id: 'elem_' + elemIdCounter,
			type: type,
			x: 5, y: 5,
			width: sz[0], height: sz[1],
			properties: defaults[type] || {}
		};

		if (!layout.elements) layout.elements = [];
		layout.elements.push(el);
		selectElement(el.id);
	}

	function duplicateSelected() {
		var el = getSelected();
		if (!el) return;
		elemIdCounter++;
		var clone = JSON.parse(JSON.stringify(el));
		clone.id = 'elem_' + elemIdCounter;
		clone.x += 3;
		clone.y += 3;
		layout.elements.push(clone);
		selectElement(clone.id);
	}

	function deleteSelected() {
		if (!selectedId) return;
		layout.elements = (layout.elements || []).filter(function(el) { return el.id !== selectedId; });
		selectedId = null;
		renderAll();
		updateProps();
	}

	function setProp(key, value) {
		var el = getSelected();
		if (!el) return;
		el[key] = parseFloat(value);
		renderAll();
	}

	function setSubProp(key, value) {
		var el = getSelected();
		if (!el) return;
		if (!el.properties) el.properties = {};
		el.properties[key] = value;
		renderAll();
		setTimeout(updateProps, 50);
	}

	// ── Save ──────────────────────────────────────────────────

	function save() {
		var statusEl = document.getElementById('mdd-save-status');
		statusEl.textContent = 'Saving...';
		statusEl.className = 'mdd-save-indicator saving';

		var formData = new FormData();
		formData.append('token', data.token);
		formData.append('action', 'save_layout');
		formData.append('id', data.id);
		formData.append('ajax', '1');
		formData.append('layout_json', JSON.stringify(layout));

		fetch(data.saveUrl, { method: 'POST', body: formData })
			.then(function(r) { return r.json(); })
			.then(function(resp) {
				statusEl.textContent = resp.success ? 'Saved' : 'Save failed';
				statusEl.className = 'mdd-save-indicator' + (resp.success ? ' saved' : '');
				setTimeout(function() { statusEl.textContent = ''; }, 2000);
			})
			.catch(function() {
				statusEl.textContent = 'Error';
				statusEl.className = 'mdd-save-indicator';
			});
	}

	// ── Public API ────────────────────────────────────────────

	return { init: init };
})();
