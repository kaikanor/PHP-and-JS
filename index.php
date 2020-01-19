<?php
if (isset($_GET['id_ajax'])) // Пришёл запрос на курс с айдишником.
	{
	if ($_GET['id_ajax'] == 1) // Добавить курс
	{
		try
		{
			$db = new SQLite3("db.sqlite3");
			$db->enableExceptions(true);
			$db->query('CREATE TABLE "'.$_GET['par'].'" ("users" TEXT)');
			echo "Курс ".$_GET['par']." успешно добавлен.";
		}
		catch (Exception $e)
		{
			if ($db->lastErrorMsg() == 'table "'.$_GET['par'].'" already exists')
			{
				echo "Курс ".$_GET['par']." уже был добавлен ранее.";
			}
			else
			{
				echo $db->lastErrorMsg();
			}
		}
		$db -> close();
		return;
	}

	if ($_GET['id_ajax'] == 2) // Удалить курс
	{
		$users = array();
		$db = new SQLite3("db.sqlite3");
		$statement = $db->prepare('SELECT * FROM '.$_GET['par']);
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		while ($mas)
		{
			array_push($users, $mas['users']);
			$mas = $result->fetchArray(SQLITE3_ASSOC);
		}
		foreach ($users as $key => $value)
		{
			$db->query('DELETE FROM "users" WHERE id='.$value);
		}
		try
		{
			$db->enableExceptions(true);
			$db->query('DROP TABLE "'.$_GET['par'].'"');
			echo "Курс ".$_GET['par']." успешно удален.";
		}
		catch (Exception $e)
		{
			if ($db->lastErrorMsg() == "no such table: ".$_GET['par'])
			{
				echo "Курса ".$_GET['par']." нет в базе.";
			}
			else
			{
				echo $db->lastErrorMsg();
			}
		}
		$db -> close();
		return;
	}

	if ($_GET['id_ajax'] == 3) // Добавить предмет
	{
		try
		{
			$db = new SQLite3("db.sqlite3");
			$db->enableExceptions(true);
			$db->query('ALTER TABLE "'.$_GET['par'].'" ADD COLUMN "'.$_GET['par2'].'" INTEGER DEFAULT 1');
			echo "Предмет ".$_GET['par2']." успешно добавлен к курсу ".$_GET['par'].".";
		}
		catch (Exception $e)
		{
			if ($db->lastErrorMsg() == 'no such table: '.$_GET['par'])
			{
				echo "Курса ".$_GET['par']." нет в базе.";
			}
			elseif ($db->lastErrorMsg() == 'duplicate column name: '.$_GET['par2'])
			{
				echo "Предмет ".$_GET['par2']." уже был добавлен ранее.";
			}
			else
			{
				echo $db->lastErrorMsg();
			}
		}
		$db -> close();
		return;
	}

	if ($_GET['id_ajax'] == 4) // Удалить предмет
	{
		try
		{
			$db = new SQLite3("db.sqlite3");
			$db->enableExceptions(true);
			$query = "";
			$statement_for_subjects = $db->prepare('pragma table_info("'.$_GET['par'].'")');
			$result_for_subjects = $statement_for_subjects->execute();
			$mas_for_subjects = $result_for_subjects->fetchArray(SQLITE3_ASSOC);
			$flag = true;
			while ($mas_for_subjects)
			{
				if ($mas_for_subjects['name'] != $_GET['par2'])
				{
					if ($query == "")
					{
						$query .= $mas_for_subjects['name'];
					}
					else
					{
						$query .= ", ".$mas_for_subjects['name'];
					}
				}
				else
				{
					$flag = false;
				}
				$mas_for_subjects = $result_for_subjects->fetchArray(SQLITE3_ASSOC);
			}
			if ($flag)
			{
				echo "Такого предмета нет на курсе ".$_GET['par'];
				return;
			}

			$statement = $db->prepare('SELECT '.$query.' FROM '.$_GET['par']);
			$result = $statement->execute();
			$mas = $result->fetchArray(SQLITE3_ASSOC);
			$db->query('CREATE TABLE "'.$_GET['par'].'_new" AS SELECT '.$query.' FROM '.$_GET['par']);
			$db -> close();
			$db = new SQLite3("db.sqlite3");
			$db->query('DROP TABLE "'.$_GET['par'].'"');
			$db->query('ALTER TABLE "'.$_GET['par'].'_new" RENAME TO "'.$_GET['par'].'"');
			echo "Предмет ".$_GET['par2']." успешно удален из курса ".$_GET['par'].". Курс смещен в конец списка курсов.";
		}
		catch (Exception $e)
		{
			if ($db->lastErrorMsg() == 'no such table: '.$_GET['par'])
			{
				echo "Курса ".$_GET['par']." нет в базе.";
			}
			elseif ($db->lastErrorMsg() == 'duplicate column name: '.$_GET['par2'])
			{
				echo "Предмет ".$_GET['par2']." уже был добавлен ранее.";
			}
			else
			{
				echo $db->lastErrorMsg();
			}
		}
		$db -> close();
		return;
	}

	if ($_GET['id_ajax'] == 5) // Добавить студентов
	{
		$db = new SQLite3("db.sqlite3");
		$db->enableExceptions(true);
		$statement = $db->prepare('SELECT id FROM "users"');
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		$unik_id = array();
		$unik_id_add = array();
		array_push($unik_id, $mas['id']);
		while ($mas)
		{
			array_push($unik_id, $mas['id']);
			$mas = $result->fetchArray(SQLITE3_ASSOC);
		}
		for ($i=0; $i < (int)$_GET['par2']; $i++) 
		{ 
			$id = rand();
			while (in_array($id, $unik_id))
			{
				$id = rand();
			}
			array_push($unik_id, $id);
			array_push($unik_id_add, $id);
			try
			{
				$db->query('INSERT INTO "'.$_GET['par'].'" (users) VALUES("'.$id.'")');
				$db->query('INSERT INTO "users" (id, course) VALUES("'.$id.'", "'.$_GET['par'].'")');
			}
			catch (Exception $e)
			{
				if ($db->lastErrorMsg() == 'no such table: '.$_GET['par'])
				{
					echo "Курса ".$_GET['par']." нет в базе.";
					return;
				}
				else
				{
					echo $db->lastErrorMsg();
				}
			}
		}
		echo $_GET['par2']." студентов успешно добавлены к курсу ".$_GET['par'].". \n";
		$str_users = "";
		foreach ($unik_id_add as $key => $value)
		{
			$str_users .= "$value \n";
		}
		echo $str_users;
		$db -> close();
		return;
	}

	if ($_GET['id_ajax'] == 6) // Удалить студента
	{
		$db = new SQLite3("db.sqlite3");
		$db->enableExceptions(true);
		$statement = $db->prepare('SELECT course FROM users WHERE id='.$_GET['par']);
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		$course = $mas['course'];
		$db->query('DELETE FROM users WHERE id='.$_GET['par']);
		$db->query('DELETE FROM '.$course.' WHERE users='.$_GET['par']);
		echo "Идентификатор номер ".$_GET['par']." успешно удалён.";
		$db -> close();
		return;
	}
	if ($_GET['id_ajax'] == 7) // Изменить оценки
	{
		$db = new SQLite3("db.sqlite3");
		$db->enableExceptions(true);
		$statement = $db->prepare('SELECT * FROM '.$_GET['par2'].' WHERE users='.$_GET['par3']);
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		$i = 0;
		foreach (array_slice($mas, 1) as $key => $value)
		{
			$db->query('UPDATE '.$_GET['par2'].' SET '.$key.' = "'.$_GET['par'][$i].'" WHERE users='.$_GET['par3']);
			$i++;
		}
		echo "ok";
		$db -> close();
		return;
	}
}

if (!(file_exists("db.sqlite3"))) // Запускаемся первый раз.
{
	echo "<head>";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<link rel = "stylesheet" href = "style_err.css" type = "text/css" />';
	echo "</head>";
	echo "<body>";
	echo "<title>Идентификатор администратора</title>";
	$id = rand();
	$db = new SQLite3("db.sqlite3");
	$db->query('CREATE TABLE "users" ("id" INTEGER PRIMARY KEY, "course" TEXT)');
	$db->query('INSERT INTO "users" ("id", "course") VALUES ('.$id.', "admin")');
	echo '<p id="cont">'.$id.'</p>';
	echo '<button onclick = "copy()">Копировать</button>';
	echo '<p><input type=button value="Обновить страницу, чтобы начать работать." onclick = "window.location.reload()">';
	$db -> close();
	echo "
		<script>
		function copy()
		{
			alert('Если идентификатор не получилось скопировать, то его необходимо запомнить или скопировать вручную.');
			try
			{
				navigator.clipboard.writeText(document.getElementById('cont').innerHTML);
			}
			catch (err)
			{
				try
				{
					var copytext = document.createElement('input');
					copytext.value = document.getElementById('cont').innerHTML;
					document.body.appendChild(copytext);
					copytext.select();
					document.execCommand('copy');
					document.body.removeChild(copytext);
				}
				catch (err)
				{
					alert('Не получилось копировать идентификатор. Скопируйте вручную.');
				}
			}
		}
		</script>
	";
	return;
}
if (isset($_COOKIE['id']))
{
	$_POST['id'] = $_COOKIE['id'];
	$course = $_COOKIE['course'];
}
if (isset($_POST['id'])) 
{
	$db = new SQLite3("db.sqlite3");
	if (!(isset($course)))
	{
		$statement = $db->prepare('SELECT "course" FROM "users" WHERE "id" = "'.$_POST['id'].'"');
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		if ($mas == false) 
		{
			echo "<head>";
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
			echo "</head>";
			echo "<body>";
			echo "<title>Ошибка</title>";
			echo '<link rel = "stylesheet" href = "style_err.css" type = "text/css" />';
			echo "<br>Такого идентификатора не существует.";
			echo '<p><input type=button value="Попробовать ещё раз" onclick = "history.back()">';
			return;
		}
		$course = $mas["course"];
	}
	if ($course == "admin") 
	{
		setcookie("course", "admin", time() + 3600);
		setcookie("id", $_POST['id'], time() + 3600);
		echo "<head>";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo "</head>";
		echo "<body>";
		echo "<title>Административная панель</title>";
		echo '<link rel = "stylesheet" href = "style_admin.css" type = "text/css" />';
		echo '<p><a href = "new.php">Перейти к возможностям удаления и создания тестовой базы данных</a>';
		echo '<p>Введите курс: <input type = "text" id = "course">';
		echo '<input type=button value="Добавить" onclick = "add_course()">';
		echo '<input type=button value="Удалить" onclick = "del_course()">';
		echo '<p>Введите предмет: <input type = "text" id = "subject">';
		echo '<input type=button value="Добавить предмет" onclick = "add_subject()">';
		echo '<input type=button value="Удалить предмет" onclick = "del_subject()">';
		echo '<p>Введите количество студентов: <input type = "text" id = "kol_users">';
		echo '<input type=button value="Добавить студентов" onclick = "add_users()">';
		echo '<p>Введите номер студента: <input type = "text" id = "del_users">';
		echo '<input type=button value="Удалить студента" onclick = "del_users()"> После ввода цифр можно двигаться по списку клавишами вверх и вниз и выбирать по Enter.';
		echo '<p><div id = "help" onclick = "copy_help()"> </div>';
		$ans = array();
		$statement = $db->prepare('SELECT name FROM sqlite_master WHERE type = "table"');
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		$id_html = 1;
		while ($mas) 
		{
			$course = $mas['name'];
			if ($course == "users")
			{
				$statement_for_users = $db->prepare('SELECT * FROM "'.$course.'"');
				$result_for_users = $statement_for_users->execute();
				$mas_for_users = $result_for_users->fetchArray(SQLITE3_ASSOC);
				echo "<br><input type=button value='Пользователи' onclick='show_hide($id_html, ".'"users"'.")'></br><div id = ".'"div_'.$id_html.'"'."><table border=1 id=$id_html>";
				$id_html += 1;
				echo "<tr><td>Идентификатор</td><td>Курс</td></tr>";
				while ($mas_for_users) 
				{
					echo "<tr><td>".$mas_for_users['id']."</td><td>".$mas_for_users['course']."</td></tr>";
					$mas_for_users = $result_for_users->fetchArray(SQLITE3_ASSOC);
				}
				echo "</table></div>";
				$mas = $result->fetchArray(SQLITE3_ASSOC);
				continue;
			}
			$ans[$course] = array();
			$statement_for_subjects = $db->prepare('pragma table_info("'.$course.'")');
			$result_for_subjects = $statement_for_subjects->execute();
			$mas_for_subjects = $result_for_subjects->fetchArray(SQLITE3_ASSOC);
			$ans[$course]["users"] = array();
			while ($mas_for_subjects)
			{
				if ($mas_for_subjects['name'] != 'users')
				{
					array_push($ans[$course]["users"], $mas_for_subjects['name']);
				}	
				$mas_for_subjects = $result_for_subjects->fetchArray(SQLITE3_ASSOC);
			}
			$statement_for_marks = $db->prepare('SELECT * FROM "'.$course.'"');
			$result_for_marks = $statement_for_marks->execute();
			$mas_for_marks = $result_for_marks->fetchArray(SQLITE3_ASSOC);
			while ($mas_for_marks) 
			{
				$users = $mas_for_marks['users'];
				$ans[$course][$users] = array_slice($mas_for_marks, 1);
				$mas_for_marks = $result_for_marks->fetchArray(SQLITE3_ASSOC);
			}
			$mas = $result->fetchArray(SQLITE3_ASSOC);
		}
		$str_tables = "";
		foreach ($ans as $key => $value) 
		{
			echo "<input type=button value='$key' onclick='show_hide($id_html, ".'"'.$key.'"'.")'>";
			$str_tables .= "<div id = ".'"div_'.$id_html.'"'."><table border=1 id=$id_html>";
			$id_html += 1;
			foreach ($value as $key_course => $value_course)
			{
				$str_tables .= "<tr><td>$key_course</td>";
				foreach ($value_course as $key_mark => $value_mark) 
				{
					$str_tables .= "<td>$value_mark</td>";
				}
				$str_tables .= "</tr>";
			}
			$str_tables .= "</table></div>";
		}
		echo $str_tables;
		$db -> close();
	}
	else
	{
		setcookie("course", $course, time() + 3600);
		setcookie("id", $_POST['id'], time() + 3600);
		echo "<head>";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo '<link rel = "stylesheet" href = "style_user.css" type = "text/css" />';
		echo "</head>";
		echo "<body>";

		echo "<title>".$course."</title>";
		$statement = $db->prepare('SELECT * FROM '.$course.' WHERE "users" = "'.$_POST['id'].'"');
		$result = $statement->execute();
		$mas = $result->fetchArray(SQLITE3_ASSOC);
		$mas_of_subjects = array();
		while ($mas)
		{
			foreach (array_slice($mas, 1) as $key => $value)
			{
				echo "$key<br>";
				$select = "\n <select id = subject_$key>";
				array_push($mas_of_subjects, $key);
				for ($i=1; $i < 6; $i++)
				{
					if ($value == $i)
					{
						$select .= '<option selected value="'.$i.'">'.$i.'</option>';
					}
					else
					{
						$select .= '<option value="'.$i.'">'.$i.'</option>';
					}
				}
				$select .= "</select>";
				echo "$select<br>";
			}
			$mas = $result->fetchArray(SQLITE3_ASSOC);
		}
		echo '<p><input type=button value="Установить оценки" onclick = "update_marks()">';
		echo "\n <script>";
		echo "\n function update_marks() { param = ''; ";
		foreach ($mas_of_subjects as $key => $value)
		{
			echo "\n param += document.getElementById('subject_".$value."').options.selectedIndex + 1; ";
		}
		echo "\n param += '&par2= ".$course."'; ";
		echo "\n param += '&par3= ".$_POST['id']."'; ";
		echo "\n var ans = SendRequest(7, param); ";
		echo "\n alert(ans)" ;
		echo "\n window.location.reload(); ";
		echo "\n  } </script>";

		echo "
		<script>
			function SendRequest(id, param) 
			{
				var xhr = new XMLHttpRequest();
				xhr.open('GET', 'index.php?id_ajax=' + id + '&par=' + param, false);
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
		</script>";
		return;
	}
}
else
{
	echo "<head>";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo "</head>";
	echo "<body>";
	// Запрос на курс
	echo '<link rel = "stylesheet" href = "style_login.css" type = "text/css" />';
	echo '<form action="index.php" method="POST">
      	  <p>Введите идентификатор: <input type = "text" name = "id" autocomplete = "off">
          <p><input type=submit value="Отправить">
          </form>';
}

?>
<script type="text/javascript">
	document.getElementById("course").value = "";
	document.getElementById("subject").value = "";
	document.getElementById("kol_users").value = "";
	document.getElementById("del_users").value = "";

	function del_users()
	{
		user = input_help.value;
		if (user.length == 0)
		{
			alert("Введите пользователя для удаления");
			return;
		}
		mas_for_users = Array.from(document.getElementById("1").rows).map(function(elem) { return elem.cells[0].innerHTML } );
		ttt = Array.from(document.getElementById("1").rows).map(function(elem) { if (elem.cells[0].innerHTML == user) return elem.cells[1].innerHTML } );

		if (ttt.indexOf("admin") != -1)
		{
			alert("Нельзя удалить администратора.");
			return;
		}

		if (mas_for_users.indexOf(user) == -1)
		{
			alert("Такого пользователя нет в системе");
			return;
		}
		var ans = SendRequest(6, user);
		alert(ans);
		window.location.reload();
	}
	var input_help = document.getElementById("del_users");
	var div_help = document.getElementById("help");
	var choose = -1;
	var number_of_help = 5;
	var kol_of_help = 0;
	var div = document.createElement('div');
	div.innerHTML = "Курс не выбран.";
	var parentElem = document.body;
	parentElem.insertBefore(div, parentElem.children[1]);

	for (var i = 1; i < <?php echo $id_html; ?>; i++) 
	{
		document.getElementById(i).style.display = "none";
	}
	var name_choose = "";
	input_help.oninput = function()
	{
		choose = -1;
		div_help.innerHTML = "";
		kol_of_help = 0;
		i = 1;
		while (document.getElementById("1").rows[i].cells[0].innerHTML)
		{
			if (document.getElementById("1").rows[i].cells[0].innerHTML.indexOf(input_help.value) != -1)
			{
				div_help.innerHTML += "<div id=kol"+kol_of_help+">" + document.getElementById("1").rows[i].cells[0].innerHTML + "</div>";
				kol_of_help++;
			}
			if (kol_of_help > number_of_help - 1)
			{
				break;
			}
			i++;
		}
	}
		
	function add_users()
	{
		var kol_users = document.getElementById('kol_users').value;
		if (isNaN(kol_users))
		{
			alert("Введите корректное число.");
			return;
		}
		if (kol_users < 1)
		{
			alert("Количество добавляемых студентов должно быть больше 0");
			return;
		}
		kol_users = +kol_users;
		if (kol_users % 1 != 0)
		{
			alert("Введите целое число.");
			return;
		}
		div = window.div;
		if (div.innerHTML == "Курс не выбран.")
		{
			course = document.getElementById('course').value;
			if (course.length == 0)
			{
				alert("Введите название курса или выберите его.");
				return;
			}
		}
		else
		{
			if (div.innerHTML == "Выбрана таблица с пользователями")
			{
				alert("Нельзя добавить предмет в таблицу с пользователями. Необходимо выбрать таблицу с курсом.");
				return;
			}
			course = div.innerHTML.split(" ");
			course = course[2];
			course = course.replace(".", "");
			course = course.replace(/'/g, "");

		}
		var param = course + "&par2=" + kol_users;
		var ans = SendRequest(5, param);
		if (ans.split(" ")[2] != "успешно")
		{
			alert(ans);
		}
		else
		{
			alert(ans);
			i = 0;
			arr = ans.split(" ").slice(i);
			while (arr.length != kol_users + 1)
			{
				arr = ans.split(" ").slice(i);
				i++;
			}
			var str_mas = "";
			arr.forEach(function(item, i, arr) { str_mas += item + " \n\r"; });
			flag = false;
			try
			{
				navigator.clipboard.writeText(str_mas.slice(0,-4));
				flag = true;
			}
			catch (err)
			{
				try
				{
					var copytext = document.createElement('input');
					copytext.value = str_mas.slice(0,-4);
					document.body.appendChild(copytext);
					copytext.select();
					document.execCommand('copy');
					document.body.removeChild(copytext);
					flag = true;
				}
				catch (err)
				{
					var spisok = document.createElement('div');
					var str_mas = "";
					arr.forEach(function(item, i, arr) { str_mas += item + "<br>"; });
					spisok.innerHTML = "Список студентов: <br>" + str_mas.slice(0,-4);
					document.body.appendChild(spisok);
					alert("Список студентов находится внизу страницы. Чтобы студенты отобразились в таблице, необходимо перезагрузить страницу.");
				}
			}

		}
		if (flag)
		{
			document.location.href = "index.php";	
		}
	}

	function del_subject()
	{
		var subject = document.getElementById('subject').value;
		if (subject.length == 0)
		{
			alert("Введите название предмета.");
			return;
		}
		if (!(/[а-яА-Яa-zA-Z]/.test(subject[0])))
		{
			alert("Название предмета должно начинаться с буквы.");
			return;
		}
		if (div.innerHTML == "Курс не выбран.")
		{
			course = document.getElementById('course').value;
			if (course.length == 0)
			{
				alert("Введите название курса или выберите его.");
				return;
			}
		}
		else
		{
			if (div.innerHTML == "Выбрана таблица с пользователями")
			{
				alert("В таблице с пользователями не может быть предметов. Необходимо выбрать таблицу с курсом.");
				return;
			}
			course = div.innerHTML.split(" ");
			course = course[2];
			course = course.replace(".", "");
			course = course.replace(/'/g, "");
		}
		var param = course + "&par2=" + subject;
		var ans = SendRequest(4, param);
		alert(ans);
		alert("Теперь все студенты, которые будут добавлены к данному курсу, автоматически получат пустые значения в старых предметах. Это удобно в качестве некоторой возможности отслеживания студентов, которые были добавлены к курсу, после удаления предмета.");
		window.location.reload();
	}

	function add_subject()
	{
		var subject = document.getElementById('subject').value;
		if (subject.length == 0)
		{
			alert("Введите название предмета.");
			return;
		}
		if (!(/[а-яА-Яa-zA-Z]/.test(subject[0])))
		{
			alert("Название предмета должно начинаться с буквы.");
			return;
		}
		if (div.innerHTML == "Курс не выбран.")
		{
			course = document.getElementById('course').value;
			if (course.length == 0)
			{
				alert("Введите название курса или выберите его.");
				return;
			}
		}
		else
		{
			if (div.innerHTML == "Выбрана таблица с пользователями")
			{
				alert("Нельзя добавить предмет в таблицу с пользователями. Необходимо выбрать таблицу с курсом.");
				return;
			}
			course = div.innerHTML.split(" ");
			course = course[2];
			course = course.replace(".", "");
			course = course.replace(/'/g, "");
		}
		var param = course + "&par2=" + subject;
		var ans = SendRequest(3, param);
		alert(ans);
		window.location.reload();
	}

	function add_course()
	{
		var course = document.getElementById('course').value;
		if (course.length == 0)
		{
			alert("Введите название курса.");
			return;
		}
		if (!(/[а-яА-Яa-zA-Z]/.test(course[0])))
		{
			alert("Название курса должно начинаться с буквы.");
			return;
		}
		var ans = SendRequest(1, course);
		alert(ans);
		window.location.reload();
	}

	function del_course(course)
	{
		if (div.innerHTML == "Курс не выбран.")
		{
			var course = document.getElementById('course').value;
		}
		else
		{
			course = name_choose;
		}		
		if (course.length == 0)
		{
			alert("Введите название курса или выберите курс. Выбранный курс в приоритете.");
			return;
		}
		if (!(/[а-яА-Яa-zA-Z]/.test(course[0])))
		{
			alert("Название курса должно начинаться с буквы.");
			return;
		}
		var ans = SendRequest(2, course);

		alert(ans);
		window.location.reload();
	}

	function SendRequest(id, param) 
	{
		var xhr = new XMLHttpRequest();
		xhr.open('GET', 'index.php?id_ajax=' + id + '&par=' + param, false);
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

	function show_hide(id_elem, name_course) 
	{
		document.getElementById("div_table").innerHTML = document.getElementById("div_" + id_elem).innerHTML.replace("display: none", "display: inline-block");
		if (name_course != "users")
		{
			div.innerHTML = "Выбран курс '" + name_course + "'. Нажмите Delete или кнопку 'удалить', чтобы удалить.";
			name_choose = name_course;
		}
		else
		{
			div.innerHTML = "Выбрана таблица с пользователями. Удалить не получится.";	
		}
	}

	document.onkeydown = function(e)
	{
		// Delete = 46 Enter = 13 Вниз = 40 Вверх = 38
		if (e.keyCode == 13)
		{
			if (document.activeElement == input_help)
			{
				if (choose != -1)
				{
					input_help.value = document.getElementById("kol" + choose).innerHTML;
				}
			}
		}
		if (e.keyCode == 38)
		{
			if (document.activeElement == input_help)
			{
				if (choose > 0)
				{
					document.getElementById("kol" + choose).style.color = "red";
					choose--;
					document.getElementById("kol" + choose).style.color = "blue";
				}
			}	
		}

		if (e.keyCode == 40)
		{
			if (document.activeElement == input_help)
			{
				if (choose < kol_of_help - 1)
				{
					if (choose > -1)
					{
						document.getElementById("kol" + choose).style.color = "red";
					}
					choose++;
					document.getElementById("kol" + choose).style.color = "blue";
				}
			}	
		}
		
		if (e.keyCode == 46)
		{
			if (name_choose == "users")
			{
				alert("Таблицу с пользователями нельзя удалить.");
				return;
			}
			if (name_choose == "")
			{
				return;
			}
			del_course(name_choose);
		}
	}
</script>
<div id = "div_table"></div>
</body>