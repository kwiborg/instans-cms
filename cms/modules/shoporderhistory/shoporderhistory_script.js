
	function offsetfunc(A){
		F = document.forms[0];
		X = F.offset.value * 1 + A;
		if (X >= 0){
			F.offset.value = X;
		} else {
			F.offset.value = 0;
		}
		F.submit()
	}

