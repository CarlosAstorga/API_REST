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
        $this->Token    = $data->Token  ?? '';
        $this->id       = $data->id     ?? '';
        $this->Title    = $data->Title  ?? '';
        $this->Year     = $data->Year   ?? '';
        $this->imdbID   = $data->imdbID ?? '';
        $this->Poster   = $data->Poster ?? '';
        $this->Type     = $data->Type   ?? '';
    }

    public function list($data)
    {
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
            return Response::json(200, 'Movie deleted successfully');
        } else {
            return Response::json(500);
        }
    }

    public function getMovie($id)
    {
        return parent::find($id);
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
}
