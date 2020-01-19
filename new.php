<?php
if (isset($_GET['del']))
{
	if (is_file("db.sqlite3"))
	{
		unlink("db.sqlite3");
		echo "ok";
	}
	else 
	{
		echo "Файла уже не существует";
	}
	return;
}
if (isset($_GET['create']))
{
	if (is_file("db.sqlite3"))
	{
		echo "Файл сначала надо удалить.";
		return;
	}
	$db = new SQLite3("db.sqlite3");
	$id = rand();
	echo "$id ";
	$db->query('CREATE TABLE "users" ("id" INTEGER PRIMARY KEY, "course" TEXT)');
	$db->query('INSERT INTO "users" ("id", "course") VALUES ('.$id.', "admin")');
	$unik_id = array();
	array_push($unik_id, $id);

	for ($i=0; $i < rand(5, 15); $i++) 
	{ 
		$name = "Курс".$i;
		$db->query('CREATE TABLE "'.$name.'" ("users" TEXT)');

		$kol = rand(5, 10);
		for ($j=0; $j < $kol; $j++) 
		{
			$subject = "subject".$j;
			$db->query('ALTER TABLE "'.$name.'" ADD COLUMN "'.$subject.'" INTEGER DEFAULT 1');
		}
		for ($j=0; $j < rand(5, 30); $j++) 
		{
			$id = rand();
			while (in_array($id, $unik_id))
			{
				$id = rand();
			}
			array_push($unik_id, $id);
			$marks = "";
			for ($k=0; $k < $kol; $k++) 
			{
				$marks .= ", ".rand(2, 5);
			}
			$db->query('INSERT INTO "'.$name.'" VALUES("'.$id.'"'.$marks.')');
			$db->query('INSERT INTO "users" ("id", "course") VALUES ('.$id.', "'.$name.'")');
		}
	}
	echo "ok";
	$db -> close();
	return;
}

?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
</head>
<body>
<p><input type=button value="Удалить куки, которые отвечают за сохранение сессии." onclick = "delete_cookies()">
<p><input type=button value="Удалить БД" onclick = "del()">
<p><input type=button value="Создать БД с тестовыми данными" onclick = "create()">
<p><a href="index.php">Перейти к работе. Если БД нет, то будет создана пустая только с администратором.</a>
</body>
<script type="text/javascript">
	function delete_cookies()
	{
		if (document.cookie)
		{
			alert(document.cookie + " будут удалены.");
  			var cookie_date = new Date();
  			cookie_date.setTime(cookie_date.getTime() - 3600);
  			document.cookie = "course=; expires=" + cookie_date.toGMTString(); // Потому что в некоторых случаях SetCookie работает только
  			document.cookie = "id=; expires=" + cookie_date.toGMTString(); // если до него ничего не было выведено.
		}
		else
		{
			alert("Cookies нет.");
		}
		
	}

	function del()
	{
		var ans = SendRequest("del");
		if (ans != "ok")
		{
			alert(ans);
		}
		else
		{
			alert("База успешно удалена.");
		}
	}

	function create()
	{
		alert("База будет создана. Это займёт некоторое время.");
		var ans = SendRequest("create");
		mas_ans = ans.split(" ");
		if (mas_ans[1] == "ok")
		{
			flag = false;
			try
			{
				navigator.clipboard.writeText(mas_ans[0]);	
				flag = true;
			}
			catch (err)
			{
				try 
				{
					var copytext = document.createElement('input');
					copytext.value = mas_ans[0];
					document.body.appendChild(copytext);
					copytext.select();
					document.execCommand('copy');
					document.body.removeChild(copytext);
					flag = true;
				}
				catch (err)
				{
					flag = false;
				}
			}
			if (flag)
			{
				alert('База оценок успешно создана. Идентификатор администратора равен ' + mas_ans[0] + ".");
			}
			else
			{
				alert('База оценок успешно создана. Идентификатор администратора равен ' + mas_ans[0] + ". Его необходимо запомнить!");
				var div = document.createElement('div');
				div.innerHTML = "Идентификатор администратора равен: " + mas_ans[0];
				document.body.appendChild(div);
			}

		}
		else
		{
			alert('Произошла ошибка. Базу сначала надо удалить.');
		}
	}

	function SendRequest(param) 
	{
		var xhr = new XMLHttpRequest();
		xhr.open('GET', 'new.php?' + param + '=1', false);
		xhr.send(null);
		if (xhr.status != 200)
		{
			return (xhr.status + ': ' + xhr.statusText);
		}
		else
		{
			return xhr.responseText;
		}
	}

</script>