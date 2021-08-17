<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<title>Movies API</title>
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We"
			crossorigin="anonymous"
		/>
	</head>

	<body>
		<div class="container mt-3 mt-lg-5">
			<div class="row">
				<div class="col-12">
					<p class="h1">Usage</p>
					<hr />
				</div>
				<div class="col-12">
					<p class="mb-1">Send all data requests to:</p>
					<div class="bg-light py-2 w-100">
						<code class="ps-3">
							https://astorga-api-movies.herokuapp.com/movies?apikey=[yourkey]&amp;
						</code>
					</div>
				</div>
			</div>

			<div class="row mt-5">
				<div class="col-12">
					<p class="h1">Parameters</p>
				</div>
			</div>
			<hr />
			<p>By ID or Title</p>
			<div class="table-responsive">
				<table class="table table-sm table-striped table-borderless">
					<thead class="table-light">
						<tr>
							<th>Parameter</th>
							<th>Required</th>
							<th>Valid Options</th>
							<th>Default Value</th>
							<th>Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>i</td>
							<td><span class="badge bg-success">Optional*</span></td>
							<td></td>
							<td>&lt;empty&gt;</td>
							<td>A valid IMDb ID</td>
						</tr>
						<tr>
							<td>t</td>
							<td><span class="badge bg-success">Optional*</span></td>
							<td></td>
							<td>&lt;empty&gt;</td>
							<td>Movie title to search for.</td>
						</tr>
						<tr>
							<td>type</td>
							<td><span class="badge bg-secondary">No</span></td>
							<td>movie, series, episode</td>
							<td>&lt;empty&gt;</td>
							<td>Type of result to return.</td>
						</tr>
						<tr>
							<td>y</td>
							<td><span class="badge bg-secondary">No</span></td>
							<td></td>
							<td>&lt;empty&gt;</td>
							<td>Year of release.</td>
						</tr>
					</tbody>
				</table>
			</div>
			<small
				>*Please note while both "i" and "t" are optional at least one argument
				is required.
			</small>
			<hr />
			<p>By Search</p>
			<div class="table-responsive">
				<table class="table table-sm table-striped table-borderless">
					<thead class="table-light">
						<tr>
							<th>Parameter</th>
							<th>Required</th>
							<th>Valid Options</th>
							<th>Default Value</th>
							<th>Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>s</td>
							<td><span class="badge bg-success">Yes</span></td>
							<td></td>
							<td>&lt;empty&gt;</td>
							<td>Movie title to search for.</td>
						</tr>
						<tr>
							<td>type</td>
							<td><span class="badge bg-secondary">No</span></td>
							<td>movie, series, episode</td>
							<td>&lt;empty&gt;</td>
							<td>Type of result to return.</td>
						</tr>
						<tr>
							<td>y</td>
							<td><span class="badge bg-secondary">No</span></td>
							<td></td>
							<td>&lt;empty&gt;</td>
							<td>Year of release.</td>
						</tr>
						<tr>
							<td>page</td>
							<td><span class="badge bg-secondary">No</span></td>
							<td>1-100</td>
							<td>1</td>
							<td>Page number to return.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<script
			src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"
			integrity="sha384-cn7l7gDp0eyniUwwAZgrzD06kc/tftFf19TOAs2zVinnD/C7E91j9yyk5//jjpt/"
			crossorigin="anonymous"
		></script>
	</body>
</html>
