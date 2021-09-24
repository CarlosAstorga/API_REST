<?php
require_once 'Connection.php';
require_once 'Response.php';

class Movie extends Connection
{
    protected $table    = "movies";

    public function __construct($input)
    {
        parent::__construct();
        $data           = json_decode($input);
        $this->Dir      = dirname(__DIR__) . "\public\images\\";
        $this->Token    = $data->Token  ?? '';
        $this->id       = $data->id     ?? '';
        $this->Title    = $data->Title  ?? '';
        $this->Year     = $data->Year   ?? '';
        $this->imdbID   = $data->imdbID ?? '';
        $this->Poster   = '';
        $this->Type     = $data->Type   ?? '';
        $this->Image    = $data->Poster ?? '';
        $this->Path     = $this->id ? $this->Dir . "$this->id" . "\\" : $this->Dir;
    }

    public function list($data)
    {
        $this->Token    = $data['apikey'];
        if (!$this->Token)                  return Response::json(401, "Token Required");
        if (empty($this->getToken()))       return Response::json(401, "Invalid Token");

        $page           = $data['page'] ?? 1;
        $Title          = $data['s']    ?? null;
        $Type           = $data['type'] ?? null;
        $Year           = $data['y']    ?? null;

        $filters        = "";
        $query          = "SELECT * FROM $this->table";
        $countQuery     = "SELECT COUNT(*) as total FROM $this->table";
        if ($Title) $filters    = $filters . " WHERE Title LIKE '%$Title%'";
        if ($Type)  $filters    = $filters . " AND Type = '$Type'";
        if ($Year)  $filters    = $filters . " AND Year = '$Year'";

        $start      = 0;
        $take       = 10;
        if ($page > 1) {
            $start  = ($take    * ($page - 1)) + 1;
            $take   = $take     * $page;
        }

        $query      = "$query $filters limit $start, $take";
        $result     = parent::getData($query);

        return $result ? Response::json(200, '', [
            'Search'        => $result,
            'totalResults'  => parent::getData("$countQuery $filters")[0]['total']
        ]) : Response::json(200, 'Movie not found!');
    }

    public function create()
    {
        if (!$this->Token)                  return Response::json(401, "Token Required");
        if (empty($this->getToken()))       return Response::json(401, "Invalid Token");
        if (!$this->Title || !$this->Year)  return Response::json(400, "Title AND Year are required");


        $query  = "INSERT INTO " . $this->table . " (Title, Year, imdbID, Poster, Type) " . "VALUES (?, ?, ?, ?, ?)";
        $stmt   = $this->connection->prepare($query);
        $stmt->bind_param("sssss", $this->Title, $this->Year, $this->imdbID, $this->Poster, $this->Type);
        $stmt->execute();

        $movieId = $this->connection->insert_id;
        if ($movieId) {
            $stmt->close();
            if ($this->Image) {
                $this->id = $movieId;
                $this->Path = $this->Dir . "$this->id" . "\\";
                $this->savePoster();
                if ($this->Poster)
                    $this->connection->query("UPDATE $this->table SET Poster = '$this->Poster' WHERE id = $this->id");
            }
            return Response::json(200, 'Movie created successfully', ['id' => $movieId]);
        } else {
            return Response::json(500);
        }
    }

    public function update($request)
    {
        if (!$this->Token)               return Response::json(401, "Token Required");
        if (empty($this->getToken()))    return Response::json(401, "Invalid Token");
        if (!$this->id)                  return Response::json(400, "id Field Required");
        if (!parent::exists($this->id))  return Response::json(404, 'Movie not found!');

        $data       = json_decode($request, true);
        if ($data['Poster']) {
            $this->savePoster();
            $data['Poster'] = $this->Poster;
        }
        unset($data['id'], $data['Token']);

        $query      = "UPDATE " . $this->table . " SET";
        $lastKey    = array_key_last($data);

        foreach ($data as $key => $value) {
            $query      = $query . " $key = '$value'";
            if ($key !== $lastKey) {
                $query  = $query . ',';
            }
        }

        $query      = $query . " WHERE id = $this->id";
        $result     = parent::execute($query);

        return $result ?
            Response::json(200, 'Movie updated successfully', ['id' => $this->id]) :
            Response::json(400);
    }

    public function delete()
    {
        if (!$this->Token)               return Response::json(401, "Token Required");
        if (empty($this->getToken()))    return Response::json(401, "Invalid Token");
        if (!$this->id)                  return Response::json(400, "id Field Required");
        if (!parent::exists($this->id))  return Response::json(404, 'Movie not found!');

        $query  = "DELETE FROM $this->table WHERE id = ?";
        $stmt   = $this->connection->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();

        $result = $stmt->affected_rows;

        if ($result > 0) {
            $stmt->close();
            $this->removeDirectory();
            return Response::json(200, 'Movie deleted successfully');
        } else {
            return Response::json(500);
        }
    }

    public function getMovieById($id)
    {
        $query  = "SELECT * FROM $this->table WHERE imdbID = ?";
        $stmt   = $this->connection->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        if (!$result) return Response::json(404, "Movie not found!");
        $stmt->close();
        return Response::json(200, '', $result);
    }

    public function getMovieByTitle($data)
    {
        $Title          = $data['t']    ?? null;
        $Type           = $data['type'] ?? null;
        $Year           = $data['y']    ?? null;

        $filters        = "";
        $query          = "SELECT       * FROM $this->table";

        if ($Title)     $filters        = $filters . " WHERE Title LIKE ?";
        if ($Type)      $filters        = $filters . " AND Type = '$Type'";
        if ($Year)      $filters        = $filters . " AND Year = '$Year'";

        $query          = "$query $filters limit 1";

        $stmt = $this->connection->prepare($query);
        $search = "%$Title%";
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (!$result) return Response::json(404, "Movie not found!");
        $stmt->close();
        return Response::json(200, '', $result[0]);
    }

    public function getToken()
    {
        $active = 1;
        $query  = "SELECT * FROM user_token WHERE token = ? AND status = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('si', $this->Token, $active);
        $stmt->execute();
        return $stmt->get_result()->num_rows;
    }

    private function savePoster()
    {
        $split          = explode(";base64,", $this->Image);
        if (!isset($split[1]))  return;
        $mime           = mime_content_type($this->Image);
        if (!$mime)             return;
        $type           = explode('/', $mime)[1];
        $base_64        = base64_decode($split[1]);
        $file           = $this->Path . uniqid() . "." . $type;

        file_exists($this->Path) ? $this->removeFiles() : $this->createDirectoryIfNotExists();

        file_put_contents($file, $base_64);
        $this->Poster   = str_replace('\\',  '/', $file);
    }

    private function removeDirectory()
    {
        if (file_exists($this->Path)) {
            $this->removeFiles();
            rmdir($this->Path);
        }
    }

    private function removeFiles()
    {
        array_map('unlink', glob("$this->Path/*"));
    }

    private function createDirectoryIfNotExists()
    {
        if (!file_exists($this->Path)) mkdir($this->Path, 0777);
    }
}
