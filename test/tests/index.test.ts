import axios from "axios";
import {
  RegisterDto,
  LoginDto,
  ChangeUserPasswordDto,
  CreateProjectDto,
  CreateTagDto,
  EditProjectDto,
  CreateProductDto,
  EditProductDto,
} from "../src/dto";

// const API_HOST = "http://localhost:3000/api";
const API_HOST = "https://afternoon-gorge-11599.herokuapp.com/api";


/** @dev Declare bearer token to authenticate */
let access_token = "";
let projectId = "";
let productId = "";
const newPassword = "cxdsakdaskl2030h@";
const registerDto: RegisterDto = {
  username: "usertest2",
  email: "usertest2@gmail.com",
  password: newPassword,
  given_name: "Joe",
  family_name: "John",
  name: "John Joe"
};

const loginDto: LoginDto = {
  username: registerDto.username,
  password: registerDto.password
}


describe("Authentication testing", () => {
  /** @dev Register test */
  test('register a new account should be failed', async () => {
    try {
      await axios.post(`${API_HOST}/auth/register`, registerDto);
    } catch {
      expect(400).toBe(400);
    }
  });

  /** @dev Login test */
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })

  /** @dev Change password test */
  test("Change password should be successful", async () => {
    const changePasswordDto: ChangeUserPasswordDto = {
      password: newPassword,
    };
    const response = await axios.patch(`${API_HOST}/auth/password`, changePasswordDto, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    expect(response.status).toBe(200);
  })

  /** @dev Login with old password test */
  test("Login with old password should be failed", async () => {
    try {
      const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
      access_token = response?.data?.access_token;
    } catch {
      expect(400).toBe(400);
    }
  })

  /** @dev Login with new password test */
  test("Login with new password should be successfully", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, {
      ...loginDto,
      password: newPassword,
    });
    access_token = response?.data?.access_token;
    expect(response.status).toBe(200);
  })
});

describe("Project testing", () => {
  /** @dev Login*/
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })


  /** @dev Create project test */
  test("Create a project should be successful", async () => {
    try {
      const createProjectDto: CreateProjectDto = {
        name: "Project 5",
        description: "Project 1",
        image: "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0",
      };
      const response = await axios.post(`${API_HOST}/project`, createProjectDto, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      projectId = response?.data?.data?._id;
      expect(response.status).toBe(200);
    } catch {
      expect(409).toBe(409);
    }
  })

  /** @dev Edit project test */
  test("Edit a project should be successful", async () => {
    try {
      const editProjectDto: EditProjectDto = {
        name: "Project 2",
        description: "Project 2",
      };
      const response = await axios.patch(`${API_HOST}/project/${projectId}`, editProjectDto, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      projectId = response?.data?.data?._id;
      expect(response.status).toBe(200);
    } catch {
      expect(409).toBe(409);
    }
  })

  /** @dev Delete project test */
  test("Delete a project should be successful", async () => {
    console.log(projectId);
    const response = await axios.delete(`${API_HOST}/project/${projectId}`, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    projectId = response?.data?.data?._id;
    expect(response.status).toBe(200);
  })
})


describe("Tag testing", () => {
  /** @dev Get tags list*/
  test("Get tags list should be successfully", async () => {
    const response = await axios.get(`${API_HOST}/tags`);
    expect(response.status).toBe(200);
  })
})

describe("Admin testing", () => {
  const createDto: CreateTagDto = {
    name: "Template",
  };


  const adminLoginDto: LoginDto = {
    username: "tphamdn+admin@gmail.com",
    password: "dasdasdasda2dsWs",
  }

  /** @dev Login*/
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })

  /** @dev Create tag without admin role should be failed */
  test("Create tag without admin role should be failed", async () => {
    try {
      const response = await axios.post(`${API_HOST}/admin/tag`, createDto, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      expect(response.status).toBe(200);
    } catch {
      expect(403).toBe(403);
    }
  })

  /** @dev Login*/
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, adminLoginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })

  /** @dev Create tag without admin role should be failed */
  test("Create tag with admin role should be succesfully", async () => {
    try {
      const response = await axios.post(`${API_HOST}/admin/tag`, createDto, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      expect(response.status).toBe(200);
    } catch {
      expect(409).toBe(409);
    }
  })
})


let tags = [];
let projects = [];
let userId = "";
describe("Product testing", () => {
  /** @dev Login*/
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })

  /** @dev Get user info by token */
  test("Get user info by token should be successful", async () => {
    const response = await axios.get(`${API_HOST}/user/profile`, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    userId = response?.data?.data?._id;
    expect(response.status).toBe(200);
  })

  /** @dev Get project list by user */
  test("Get project list by user should be successful", async () => {
    const response = await axios.get(`${API_HOST}/projects`, {
      data: { userId }
    });

    projects = response?.data?.data;
    expect(response.status).toBe(200);
  })

  /** @dev Get tags */
  test("Get tags should be successful", async () => {
    const response = await axios.get(`${API_HOST}/tags`);
    tags = response?.data?.data;
    expect(response.status).toBe(200);
  });

  /** @dev Create product testing */
  test('Create a product should be successfully', async () => {
    try {
      const createProjectDto: CreateProductDto = {
        name: "Product 1",
        description: "Product 1",
        gallery: [
          "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0"
        ],
        projectId: projects.length > 0 ? projects[0]?._id : "",
        tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
      };
      const response = await axios.post(`${API_HOST}/product`, createProjectDto, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      productId = response?.data?.data?._id;
      expect(response.status).toBe(200);
    } catch (e) {
      expect(409).toBe(409);
    }
  })
})