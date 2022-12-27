import axios from "axios";
import {
  RegisterDto,
  LoginDto,
  CreateProductDto,
  EditProductDto,
} from "../src/dto";
import { ProductEntity } from "../src/entities";

const API_HOST = "http://localhost:3000/api";
// const API_HOST = "https://afternoon-gorge-11599.herokuapp.com/api";


/** @dev Declare bearer token to authenticate */
let access_token = "";
let id_token = "";
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


describe("Product testing", () => {
  let tags = [];
  let projects = [];
  let userId = "";
  let productId = "";
  let product: ProductEntity;

  /** @dev Get tags */
  test("Get tags should be successful", async () => {
    const response = await axios.get(`${API_HOST}/tags`);
    tags = response?.data?.data;
    expect(response.status).toBe(200);
  });

  beforeAll(async () => {
    try {
      const credentail = (await axios.post(`${API_HOST}/auth/login`, loginDto))?.data?.data;
      access_token = credentail?.access_token
      id_token = credentail?.id_token;
      console.log({ id_token });
    } catch (err) {
      console.log("Error when login", err);
    }

    try {
      userId = (await axios.get(`${API_HOST}/user/profile`, {
        data: { id_token },
        headers: { Authorization: `Bearer ${access_token}` }
      }))?.data?.data?.sub;
    } catch (err) {
      console.log("Error get tags", err);
    }

    try {
      projects = (await axios.get(`${API_HOST}/projects`, {
        data: { userId }
      }))?.data?.data;
    } catch (err) {
      console.log("Error get user projects", err);
    }
  });

  /** @dev Create product testing */
  test('Create a product should be successfully', async () => {
    try {
      const createProductDto: CreateProductDto = {
        name: "Product 10",
        description: "Product 1",
        gallery: [
          "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0"
        ],
        projectId: projects.length > 0 ? projects[0]?._id : "",
        tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
      };
      const response = await axios.post(`${API_HOST}/product`, {
        ...createProductDto,
        id_token,
      }, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      productId = response?.data?.data?._id;
      expect(response.status).toBe(200);
    } catch {
      expect(409).toBe(409);
    }
  })

  beforeEach(async () => {
    const getProductsResponse = await axios.get(`${API_HOST}/products`, {
      data: { userId }
    })

    const products = getProductsResponse.data?.data;
    if (products.length) {
      productId = products[products.length - 1]._id;
    }


    const response = await axios.get(`${API_HOST}/product/owner/${productId}`, {
      data: { id_token },
      headers: { Authorization: `Bearer ${access_token}` }
    })
    product = response?.data?.data as ProductEntity;
  });


  /** @dev Edit product testing */
  test("Edit product should be successfully", async () => {
    const editProductDto: EditProductDto = {
      name: "Product 10 edit",
      description: "Edit product 1",
      gallery: [
        ...product.gallery,
        "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0",
      ]
    }

    const response = await axios.patch(`${API_HOST}/product/${productId}`, {
      ...editProductDto,
      id_token,
    }, {
      headers: { Authorization: `Bearer ${access_token}` }
    });

    expect(response.status).toBe(200);
  })

  /** @dev Remove product testing */
  test("Remove product should be successfully", async () => {
    const response = await axios.delete(`${API_HOST}/product/${productId}`, {
      data: { id_token },
      headers: { Authorization: `Bearer ${access_token}` }
    });
    expect(response.status).toBe(200);
  })
})