<?php
require_once 'Connection.php';
require_once 'Response.php';

class Movie extends Connection
{

    private $title      = '';
    private $year       = '';
    private $imdbID     = '';
    private $poster     = '';
    private $type       = '';
    protected $table    = "movies";

    public function list($page = 1)
    {
        $start      = 0;
        $take       = 20;
        if ($page > 1) {
            $start  = ($take * ($page - 1)) + 1;
            $take   = $take * $page;
        }

        $query = "SELECT * FROM " . $this->table . " limit $start, $take";
        return parent::getData($query);
    }

    public function getMovie($id)
    {
        return parent::find($id);
    }

    public function create($request)
    {
        $data   = json_decode($request, true);
        $title  = $data['Title']        ?? '';
        $year   = $data['Year']         ?? '';

        if (!$title || !$year) return Response::json(400);

        $data           = (object)$data;
        $this->title    = $data->Title;
        $this->year     = $data->Year;
        $this->imdbID   = $data->imdbID ?? '';
        $this->poster   = $data->Poster ?? '';
        $this->type     = $data->Type   ?? '';

        $query = "INSERT INTO " . $this->table . " (Title, Year, imdbID, Poster, Type) " . "VALUES ('$this->title', '$this->year', '$this->imdbID', '$this->poster', '$this->type')";
        parent::execute($query);
        $movieId = parent::getLastId();

        if ($movieId) {
            return Response::json(200, 'Movie created successfully', ['id' => $movieId]);
        } else {
            return Response::json(500);
        }
    }

    public function update($request)
    {
        $data       = json_decode($request, true);
        $movieId    = $data['id'] ?? '';

        if (!$movieId)                  return Response::json(400);
        if (!parent::exists($movieId))  return Response::json(404);

        unset($data['id']);
        $query  = "UPDATE " . $this->table . " SET";
        $length = count($data);
        $data   = (object)$data;

        $i      = 0;
        foreach ($data as $key => $value) {
            $i++;
            $query      = $query . " $key = '$value'";
            if ($i < $length) {
                $query  = $query . ',';
            }
        }

        $query  = $query . " WHERE id = $movieId";
        $result = parent::execute($query);

        if ($result) {
            return Response::json(200, 'Movie updated successfully', ['id' => $movieId]);
        } else {
            return Response::json(400);
        }
    }

    public function delete($request)
    {
        $data       = json_decode($request, true);
        $movieId    = $data['id'] ?? '';

        if (!$movieId)                  return Response::json(400);
        if (!parent::exists($movieId))  return Response::json(404);

        $query  = "DELETE FROM " . $this->table . " WHERE id = $movieId";
        $result = parent::execute($query);

        if ($result) {
            return Response::json(200, 'Movie deleted successfully');
        } else {
            return Response::json(500);
        }
    }
}
