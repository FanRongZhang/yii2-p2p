/**
 * 左右可以进行转义
 * @param n1
 * @param n2
 */
function allsel(n1,n2)
{
  while(n1.selectedIndex!=-1)
  {
  	var indx=n1.selectedIndex;
  	var t=n1.options[indx].text;
  	var v=n1.options[indx].value;
  	n2.options.add(new Option(t,v));
  	n1.remove(indx);
  }
}

/**
 * 计算最后结果赋值给hidden
 * @param opt
 * @param hdId
 */
function setSelectedOptions(opt,hdId)
{
	var hd = document.getElementById(hdId);
	var data='';
	var len = opt.options.length;
	for(var i=0;i<len;i++)
	{
		data+=','+opt.options[i].value;
	}
	if(data!='')
		data+=',';
	hd.value=data;
}